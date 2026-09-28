<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Lacaptcha\Lacaptcha\Facades\Captcha;
use Lacaptcha\Lacaptcha\Rules\Captcha as CaptchaRule;

it('fakes a successful verification with zero real http calls', function () {
    Http::fake();

    $fake = Captcha::fake();

    $validator = Validator::make(['token' => 'anything'], ['token' => [new CaptchaRule]]);

    expect($validator->passes())->toBeTrue();

    Http::assertNothingSent();
    $fake->assertVerified();
});

it('fakes a failing verification with custom error codes', function () {
    Http::fake();

    Captcha::fake(succeeds: false, errorCodes: ['invalid-input-response']);

    $validator = Validator::make(['token' => 'anything'], ['token' => [new CaptchaRule]]);

    expect($validator->fails())->toBeTrue();
});

it('supports a per-driver override on the fake', function () {
    Http::fake();

    $fake = Captcha::fake()->forDriver('turnstile', succeeds: false);

    expect(Validator::make(['t' => 'x'], ['t' => [new CaptchaRule]])->passes())->toBeTrue()
        ->and(Validator::make(['t' => 'x'], ['t' => [new CaptchaRule('turnstile')]])->passes())->toBeFalse();

    $fake->assertVerified('hcaptcha');
    $fake->assertVerified('turnstile');
});

it('asserts nothing was verified when nothing ran', function () {
    $fake = Captcha::fake();

    $fake->assertNothingVerified();
});

it('asserts a specific verification count', function () {
    $fake = Captcha::fake();

    Validator::make(['t' => 'a'], ['t' => [new CaptchaRule]])->passes();
    Validator::make(['t' => 'b'], ['t' => [new CaptchaRule]])->passes();

    $fake->assertVerificationCount(2);
});

it('resolves an empty string driver name to the default, matching the real manager', function () {
    config()->set('captcha.default', 'turnstile');

    $fake = Captcha::fake();

    $fake->driver('')->verify('token');

    $fake->assertVerified('turnstile');
});
