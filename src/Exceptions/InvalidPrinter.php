<?php

namespace Onkbear\StarCloudPRNT\Exceptions;

use Exception;

class InvalidPrinter extends Exception
{
    public static function invalidMacAddress(): InvalidPrinter
    {
        return new static('The mac address is invalid.');
    }
}
