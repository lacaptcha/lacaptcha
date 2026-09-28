<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Lacaptcha\Lacaptcha\Rules\Captcha;

it('passes validation when the default driver verifies successfully', function () {
    Http::fake([
        'hcaptcha.com/siteverify' => Http::response(['success' => true]),
    ]);

    $validator = Validator::make(['token' => 'good-token'], ['token' => [new Captcha]]);

    expect($validator->passes())->toBeTrue();
});

it('fails validation when the default driver verification fails', function () {
    Http::fake([
        'hcaptcha.com/siteverify' => Http::response(['success' => false]),
    ]);

    $validator = Validator::make(['token' => 'bad-token'], ['token' => [new Captcha]]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('token'))->toBe(__('lacaptcha::captcha.failed'));
});

it('verifies against an explicitly requested driver, not the default', function () {
    Http::fake([
        'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        'hcaptcha.com/siteverify' => Http::response(['success' => false]),
    ]);

    $validator = Validator::make(['token' => 'good-token'], ['token' => [new Captcha('turnstile')]]);

    expect($validator->passes())->toBeTrue();

    Http::assertNotSent(fn ($request) => $request->url() === 'https://hcaptcha.com/siteverify');
});

it('forwards the request ip as remoteip', function () {
    Http::fake([
        'hcaptcha.com/siteverify' => Http::response(['success' => true]),
    ]);

    request()->server->set('REMOTE_ADDR', '10.0.0.1');

    Validator::make(['token' => 'good-token'], ['token' => [new Captcha]])->passes();

    Http::assertSent(fn ($request) => $request['remoteip'] === '10.0.0.1');
});
