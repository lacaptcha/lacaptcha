<?php

namespace Lacaptcha\Lacaptcha\Exceptions;

use RuntimeException;

class NullDriverNotAllowedException extends RuntimeException
{
    public static function make(): self
    {
        return new self(
            'The "null" captcha driver always passes verification and cannot be used outside '.
            'local/testing environments unless captcha.drivers.null.allow_in_production is set to true.',
        );
    }
}
