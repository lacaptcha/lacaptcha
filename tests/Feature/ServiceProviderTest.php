<?php

use Illuminate\Support\Facades\Blade;
use Lacaptcha\Lacaptcha\CaptchaManager;

it('registers the manager as a singleton', function () {
    expect(app(CaptchaManager::class))->toBe(app(CaptchaManager::class));
});

it('registers the captcha container alias', function () {
    expect(app('captcha'))->toBeInstanceOf(CaptchaManager::class);
});

it('merges the package config', function () {
    expect(config('captcha.drivers.hcaptcha.site_key'))->toBe('hcaptcha-site-key');
});

it('registers the captcha blade component tag', function () {
    expect(Blade::getClassComponentAliases())->toHaveKey('captcha');
});
