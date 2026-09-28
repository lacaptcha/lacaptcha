<?php

namespace Lacaptcha\Lacaptcha\Exceptions;

use InvalidArgumentException;

class UnsupportedDriverException extends InvalidArgumentException
{
    public static function make(string $driver): self
    {
        return new self("Captcha driver [{$driver}] is not supported.");
    }
}
