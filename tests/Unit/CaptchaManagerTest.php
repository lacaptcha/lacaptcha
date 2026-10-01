<?php

use Lacaptcha\Lacaptcha\CaptchaManager;
use Lacaptcha\Lacaptcha\Drivers\HcaptchaDriver;
use Lacaptcha\Lacaptcha\Drivers\RecaptchaDriver;
use Lacaptcha\Lacaptcha\Drivers\TurnstileDriver;
use Lacaptcha\Lacaptcha\Exceptions\UnsupportedDriverException;

it('resolves the default driver from config', function () {
    config()->set('captcha.default', 'turnstile');

    $manager = app(CaptchaManager::class);

    expect($manager->driver())->toBeInstanceOf(TurnstileDriver::class);
});

it('resolves a named driver distinct from the default', function () {
    $manager = app(CaptchaManager::class);

    expect($manager->driver('hcaptcha'))->toBeInstanceOf(HcaptchaDriver::class)
        ->and($manager->driver('recaptcha'))->toBeInstanceOf(RecaptchaDriver::class);
});

it('caches resolved driver instances', function () {
    $manager = app(CaptchaManager::class);

    expect($manager->driver('hcaptcha'))->toBe($manager->driver('hcaptcha'));
});

it('throws a friendly exception for an unknown driver', function () {
    $manager = app(CaptchaManager::class);

    $manager->driver('does-not-exist');
})->throws(UnsupportedDriverException::class);

it('exposes the response field name of the default or a named driver', function () {
    $manager = app(CaptchaManager::class);

    expect($manager->responseField('hcaptcha'))->toBe('h-captcha-response')
        ->and($manager->responseField('recaptcha'))->toBe('g-recaptcha-response')
        ->and($manager->responseField('turnstile'))->toBe('cf-turnstile-response')
        ->and($manager->responseField('null'))->toBe('null-response');

    config()->set('captcha.default', 'turnstile');

    expect($manager->responseField())->toBe('cf-turnstile-response');
});

it('propagates an InvalidArgumentException thrown inside a driver factory unchanged', function () {
    $manager = app(CaptchaManager::class);

    $manager->extend('broken', fn () => throw new InvalidArgumentException('bad config'));

    try {
        $manager->driver('broken');

        test()->fail('Expected exception was not thrown.');
    } catch (Throwable $e) {
        expect($e)->toBeInstanceOf(InvalidArgumentException::class)
            ->and($e)->not->toBeInstanceOf(UnsupportedDriverException::class)
            ->and($e->getMessage())->toBe('bad config');
    }
});
