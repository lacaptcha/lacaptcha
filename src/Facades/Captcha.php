<?php

namespace Lacaptcha\Lacaptcha\Facades;

use Illuminate\Support\Facades\Facade;
use Lacaptcha\Lacaptcha\CaptchaManager;
use Lacaptcha\Lacaptcha\Testing\CaptchaFake;

/**
 * @method static \Lacaptcha\Lacaptcha\Contracts\Driver driver(?string $driver = null)
 * @method static \Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse verify(string $token, ?string $remoteIp = null)
 *
 * @see CaptchaManager
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaptchaManager::class;
    }

    /**
     * Swap the manager for a fake so no real captcha provider is contacted
     * during tests.
     *
     * @param  array<int, string>  $errorCodes
     */
    public static function fake(?string $driver = null, bool $succeeds = true, array $errorCodes = []): CaptchaFake
    {
        $fake = new CaptchaFake(static::getFacadeApplication(), $driver, $succeeds, $errorCodes);

        static::swap($fake);

        return $fake;
    }
}
