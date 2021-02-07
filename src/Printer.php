<?php

namespace Onkbear\StarCloudPRNT;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Onkbear\StarCloudPRNT\Exceptions\InvalidPrinter;

class Printer extends Fluent
{
    const MAX_CHARACTERS_TWO_INCH       = 32;
    const MAX_CHARACTERS_THREE_INCH     = 48;
    const MAX_CHARACTERS_DOT_THREE_INCH = 42;
    const MAX_CHARACTERS_FOUR_INCH      = 69;

    /**
     * The printer's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'printing'          => false,
        'paper_width'       => 0,
        'print_width'       => 0,
        'dot_width'         => 0,
        'status'            => '',
        'client_type'       => '',
        'client_version'    => '',
        'last_poll'         => 0,
        'get_poll_interval' => '',
        'encodings'         => [],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'encodings' => 'array',
    ];

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    /**
     * Initialize printer.
     */
    public function __construct(string $mac)
    {
        if (! $this->isMacAddress($mac)) {
            throw InvalidPrinter::invalidMacAddress();
        }

        // Must be lower case.
        $this->device_mac = strtolower($mac);

        $this->disk = Storage::disk(config('star-cloud-prnt.storage.disk'));

        $printerFilepath = $this->getConfigFilePath();
        if ($this->disk->exists($printerFilepath)) {
            $contents = $this->disk->get($printerFilepath);
            $this->attributes = json_decode($contents, true);
        }
    }

    /**
     * Printer config file path.
     */
    protected function getConfigFilePath(): string
    {
        // For the file name, use dashes instead of colons.
        return config('star-cloud-prnt.storage.printers_path').'/'.str_replace(':', '-', $this->device_mac);
    }

    /**
     * Printer queue directory path without trailing slash.
     */
    protected function getQueueDirectoryPath(): string
    {
        // For the directory, use dashes instead of colons.
        return config('star-cloud-prnt.storage.queue_path').'/'.str_replace(':', '-', $this->device_mac);
    }

    /**
     * Validation.
     */
    protected function isMacAddress(string $mac): bool
    {
        return preg_match("/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/", $mac);
    }

    /**
     * Is printer online ?
     */
    public function isOnline(): bool
    {
        return ($this->last_poll >= time() - 60) && (substr($this->status, 0, 1) === '2');
    }

    /**
     * Save printer config to storage.
     */
    public function save()
    {
        // Update timestamp.
        $this->last_poll = time();

        $printerFilepath = $this->getConfigFilePath();
        return $this->disk->put($printerFilepath, $this->toJson());
    }

    /**
     * Update the device status and timestamp in the config.
     */
    public function setDeviceStatus(string $status): void
    {
        $this->status = $status;
        $this->last_poll = time();

        $this->save();
    }

    /**
     * Push print jon into queue
     */
    public function pushJob(string $contents, $extension): bool
    {
        $queueDirectoryPath = $this->getQueueDirectoryPath();
        $queueFilePath = $queueDirectoryPath.'/'.time().'.'.$extension;
        return $this->disk->put($queueFilePath, $contents);
    }

    /**
     * Get last print job
     */
    public function getOldestJob(): ?array
    {
        $queueDirectoryPath = $this->getQueueDirectoryPath();
        $files = $this->disk->files($queueDirectoryPath);
        if (count($files)) {
            $extension = pathinfo($files[0], PATHINFO_EXTENSION);
            $encodings = collect(config('star-cloud-prnt.supported_encordings'));
            $encoding = 'text/plain';
            foreach ($encodings as $encodingKey => $params) {
                if ($params['extension'] === $extension) {
                    $encoding = $encodingKey;
                }
            }
            return [
                'type' => $encoding,
                'contents' => $this->disk->get($files[0])
            ];
        }
        return null;
    }

    /**
     * Pop last print job from queue
     */
    public function popJob(): bool
    {
        $queueDirectoryPath = $this->getQueueDirectoryPath();
        $files = $this->disk->files($queueDirectoryPath);
        if (count($files)) {
            return $this->disk->delete($files[0]);
        }

        return false;
    }

    /**
     * Get queue count.
     */
    public function getQueueCount(): int
    {
        $queueDirectoryPath = $this->getQueueDirectoryPath();
        return count($this->disk->files($queueDirectoryPath));
    }

    /**
     * Get priority encoding.
     */
    public function getEncoding(): string
    {
        foreach (config('star-cloud-prnt.supported_encordings') as $encoding => $value) {
            if (in_array($encoding, $this->encodings)) {
                return $encoding;
            }
        }

        return 'text/plain';
    }

    /**
     * 
     */
    public function getMaxCharacter(): int
    {
        if ($this->print_width <= 58) {
            return self::MAX_CHARACTERS_TWO_INCH;
        } elseif ($this->print_width <= 72) {
            return self::MAX_CHARACTERS_THREE_INCH;
        } elseif ($this->print_width <= 112) {
            return self::MAX_CHARACTERS_FOUR_INCH;
        }

        return 0;
    }
}
