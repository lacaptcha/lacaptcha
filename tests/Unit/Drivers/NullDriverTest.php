<?php

use Lacaptcha\Lacaptcha\CaptchaManager;
use Lacaptcha\Lacaptcha\Drivers\NullDriver;
use Lacaptcha\Lacaptcha\Exceptions\NullDriverNotAllowedException;

it('resolves in the testing environment', function () {
    expect(app(CaptchaManager::class)->driver('null'))->toBeInstanceOf(NullDriver::class);
});

it('always succeeds by default when resolved', function () {
    $response = app(CaptchaManager::class)->driver('null')->verify('anything');

    expect($response->success)->toBeTrue();
});

it('refuses to resolve outside local/testing without an explicit opt-in', function () {
    app()->detectEnvironment(fn () => 'production');

    app(CaptchaManager::class)->driver('null');
})->throws(NullDriverNotAllowedException::class);

it('resolves in production when explicitly allowed via config', function () {
    app()->detectEnvironment(fn () => 'production');
    config()->set('captcha.drivers.null.allow_in_production', true);

    expect(app(CaptchaManager::class)->driver('null'))->toBeInstanceOf(NullDriver::class);
});
