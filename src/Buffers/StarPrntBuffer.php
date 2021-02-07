<?php

namespace Onkbear\StarCloudPRNT\Buffers;

/**
 * Star PRNT Mode
 * application/vnd.star.starprnt
 */
class StarPrntBuffer extends Buffer
{
    /**
     * Select emphasized printing.
     */
    public function setTextEmphasized(): void
    {
        $this->printJobBuilder .= self::SET_EMPHASIZED_HEX;
    }

    /**
     * Cancel emphasized printing.
     */
    public function cancelTextEmphasized(): void
    {
        $this->printJobBuilder .= self::CANCEL_EMPHASIZED_HEX;
    }

    /**
     * Specify left alignment.
     */
    public function setTextLeftAlign(): void
    {
        $this->printJobBuilder .= self::SET_LEFT_ALIGNMENT_HEX;
    }

    /**
     * Specify center alignment.
     */
    public function setTextCenterAlign(): void
    {
        $this->printJobBuilder .= self::SET_CENTER_ALIGNMENT_HEX;
    }

    /**
     * Specify right alignment.
     */
    public function setTextRightAlign(): void
    {
        $this->printJobBuilder .= self::SET_RIGHT_ALIGNMENT_HEX;
    }

    /**
     * Select code page.
     */
    public function setCodepage(string $codepage): void
    {
        if ($codepage == 'UTF-8') {
            $this->printJobBuilder .= '1b1d295502003001'.'1b1d295502004000';
        } elseif ($codepage == '1252') {
            $this->printJobBuilder .= self::CODEPAGE_HEX.'20';
        } else {
            $this->printJobBuilder .= self::CODEPAGE_HEX.$codepage;
        }
    }

    /**
     * Specify/cancel expanded double-wide printing.
     */
    public function setFontMagnification(int $width, int $height): void
    {
        $w = 0;
        $h = 0;

        if ($width <= 1) {
            $w = 0;
        } elseif ($width >= 6) {
            $w = 5;
        } else {
            $w = $width - 1;
        }
        
        if ($height <= 1) {
            $h = 0;
        } elseif ($height >= 6) {
            $h = 5;
        } else {
            $h = $height - 1;
        }
        
        $this->printJobBuilder .= '1B69'.'0'.$h.'0'.$w;
    }
}
