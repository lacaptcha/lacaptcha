<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Lacaptcha\Lacaptcha\CaptchaManager;

it('posts the token and secret to hcaptcha siteverify and succeeds', function () {
    Http::fake([
        'hcaptcha.com/siteverify' => Http::response(['success' => true]),
    ]);

    $response = app(CaptchaManager::class)->driver('hcaptcha')->verify('token-123', '1.2.3.4');

    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://hcaptcha.com/siteverify'
            && $request['secret'] === 'hcaptcha-secret-key'
            && $request['response'] === 'token-123'
            && $request['remoteip'] === '1.2.3.4';
    });
});

it('fails when hcaptcha siteverify returns success false', function () {
    Http::fake([
        'hcaptcha.com/siteverify' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
    ]);

    $response = app(CaptchaManager::class)->driver('hcaptcha')->verify('bad-token');

    expect($response->failed())->toBeTrue()
        ->and($response->errorCodes)->toBe(['invalid-input-response']);
});

it('fails gracefully on a connection failure', function () {
    Http::fake(function () {
        throw new ConnectionException('timed out');
    });

    $response = app(CaptchaManager::class)->driver('hcaptcha')->verify('token-123');

    expect($response->failed())->toBeTrue()
        ->and($response->errorCodes)->toBe(['connection-failed']);
});

it('fails gracefully when the provider returns a valid but non-array JSON body', function () {
    Http::fake([
        'hcaptcha.com/siteverify' => Http::response('0', 200, ['Content-Type' => 'application/json']),
    ]);

    $response = app(CaptchaManager::class)->driver('hcaptcha')->verify('token-123');

    expect($response->failed())->toBeTrue()
        ->and($response->errorCodes)->toBe(['invalid-response']);
});
