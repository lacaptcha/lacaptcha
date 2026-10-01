<?php

use Illuminate\Support\Facades\Blade;
use Lacaptcha\Lacaptcha\CaptchaManager;

it('renders no error markup when there is nothing to show', function () {
    $html = Blade::render('<x-captcha driver="hcaptcha" />');

    expect($html)->not->toContain('lacaptcha-error');
});

it('automatically shows a validation error for the driver response field', function () {
    $this->withViewErrors(['h-captcha-response' => 'The captcha verification failed. Please try again.']);

    $html = Blade::render('<x-captcha driver="hcaptcha" />');

    expect($html)->toContain('lacaptcha-error')
        ->toContain('The captcha verification failed. Please try again.');
});

it('does not show an error belonging to a different field', function () {
    $this->withViewErrors(['some-other-field' => 'Unrelated error.']);

    $html = Blade::render('<x-captcha driver="hcaptcha" />');

    expect($html)->not->toContain('lacaptcha-error');
});

it('suppresses the automatic error when showErrors is false', function () {
    $this->withViewErrors(['h-captcha-response' => 'The captcha verification failed. Please try again.']);

    $html = Blade::render('<x-captcha driver="hcaptcha" :options="[\'showErrors\' => false]" />');

    expect($html)->not->toContain('lacaptcha-error');
});

it('shows the error through the @captcha directive too, identically to the component', function () {
    $this->withViewErrors(['cf-turnstile-response' => 'The captcha verification failed. Please try again.']);

    $component = Blade::render('<x-captcha driver="turnstile" />');
    $directive = Blade::render("@captcha('turnstile')");

    expect($directive)->toBe($component)
        ->and($directive)->toContain('lacaptcha-error');
});

it('uses the correct field per driver, not a hardcoded one', function () {
    $this->withViewErrors(['cf-turnstile-response' => 'Turnstile failed.']);

    $hcaptchaHtml = Blade::render('<x-captcha driver="hcaptcha" />');
    $turnstileHtml = Blade::render('<x-captcha driver="turnstile" />');

    expect($hcaptchaHtml)->not->toContain('lacaptcha-error')
        ->and($turnstileHtml)->toContain('lacaptcha-error');
});

it('ignores a caller-supplied responseField option instead of letting it override the real one', function () {
    $this->withViewErrors(['h-captcha-response' => 'The captcha verification failed. Please try again.']);

    $html = app(CaptchaManager::class)
        ->widget('hcaptcha', ['responseField' => 'some-fake-field', 'theme' => 'dark'])
        ->render();

    // The real field's error still shows (not silently dropped in favor of
    // the bogus override), and a legitimate per-driver option alongside it
    // ('theme') still reaches the widget untouched.
    expect($html)->toContain('lacaptcha-error')
        ->toContain('The captcha verification failed. Please try again.')
        ->toContain('data-theme="dark"');
});
