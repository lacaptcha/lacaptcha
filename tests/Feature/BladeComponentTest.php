<?php

use Illuminate\Support\Facades\Blade;

it('renders the hcaptcha widget with its markers', function () {
    $html = Blade::render('<x-captcha driver="hcaptcha" />');

    expect($html)->toContain('h-captcha')
        ->toContain('js.hcaptcha.com')
        ->toContain('hcaptcha-site-key');
});

it('renders the recaptcha v2 widget with its markers', function () {
    $html = Blade::render('<x-captcha driver="recaptcha" />');

    expect($html)->toContain('g-recaptcha')
        ->toContain('google.com/recaptcha');
});

it('renders the recaptcha v3 widget as script-only with a hidden field', function () {
    config()->set('captcha.drivers.recaptcha.version', 'v3');

    $html = Blade::render('<x-captcha driver="recaptcha" />');

    expect($html)->toContain('g-recaptcha-response')
        ->toContain('grecaptcha.execute')
        ->not->toContain('class="g-recaptcha"');
});

it('renders the turnstile widget with its markers', function () {
    $html = Blade::render('<x-captcha driver="turnstile" />');

    expect($html)->toContain('cf-turnstile')
        ->toContain('challenges.cloudflare.com');
});

it('uses the configured default driver when none is specified', function () {
    config()->set('captcha.default', 'turnstile');

    $html = Blade::render('<x-captcha />');

    expect($html)->toContain('cf-turnstile');
});
