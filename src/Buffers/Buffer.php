<?php

namespace Onkbear\StarCloudPRNT\Buffers;

use Illuminate\Support\Facades\Storage;

class Buffer
{
    const NEW_LINE_HEX              = '0A';
    const SET_EMPHASIZED_HEX        = '1B45';
    const CANCEL_EMPHASIZED_HEX     = '1B46';
    const SET_LEFT_ALIGNMENT_HEX    = '1B1D6100';
    const SET_CENTER_ALIGNMENT_HEX  = '1B1D6101';
    const SET_RIGHT_ALIGNMENT_HEX   = '1B1D6102';
    const FEED_FULL_CUT_HEX         = '1B6402';
    const FEED_PARTIAL_CUT_HEX      = '1B6403';
    const CODEPAGE_HEX              = '1B1D74';

    protected $printJobBuilder = '';

    /**
     * Select emphasized printing.
     */
    public function setTextEmphasized(): void
    {
    }

    /**
     * Cancel emphasized printing.
     */
    public function cancelTextEmphasized(): void
    {
    }

    /**
     * Specify left alignment.
     */
    public function setTextLeftAlign(): void
    {
    }

    /**
     * Specify center alignment.
     */
    public function setTextCenterAlign(): void
    {
    }

    /**
     * Specify right alignment.
     */
    public function setTextRightAlign(): void
    {
    }

    /**
     * Select code page.
     */
    public function setCodepage(string $codepage): void
    {
    }

    /**
     * Specify/cancel expanded double-wide printing.
     */
    public function setFontMagnification(int $width, int $height): void
    {
    }

    /**
     * Hex.
     */
    public function addHex(string $hex): void
    {
        $this->printJobBuilder .= $hex;
    }

    /**
     * Text.
     */
    public function addText(string $text): void
    {
        $this->printJobBuilder .= $this->strToHex($text);
    }

    /**
     * Text with line break.
     */
    public function addTextLine(string $text): void
    {
        $this->printJobBuilder .= $this->strToHex($text).self::NEW_LINE_HEX;
    }

    /**
     * Line break.
     */
    public function addNewLine(int $quantity): void
    {
        for ($i = 0; $i < $quantity; ++$i) {
            $this->printJobBuilder .= self::NEW_LINE_HEX;
        }
    }

    /**
     * Partial cut.
     */
    public function addFeedPartialCut(): void
    {
        $this->printJobBuilder .= self::FEED_PARTIAL_CUT_HEX;
    }

    /**
     * Get content binary.
     */
    public function getContent(): string
    {
        return hex2bin($this->printJobBuilder.self::NEW_LINE_HEX);
    }

    /**
     * Text to hex
     */
    protected function strToHex(string $string): string
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); ++$i) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex);
    }
}
