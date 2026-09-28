<?php

use Illuminate\Support\Facades\Http;
use Lacaptcha\Lacaptcha\CaptchaManager;

it('posts to turnstile siteverify and succeeds', function () {
    Http::fake([
        'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'idempotency-key' => 'idem-1',
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('turnstile')->verify('token-abc', '5.6.7.8');

    expect($response->success)->toBeTrue()
        ->and($response->idempotencyKey)->toBe('idem-1');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret'] === 'turnstile-secret-key'
            && $request['response'] === 'token-abc'
            && $request['remoteip'] === '5.6.7.8';
    });
});

it('fails when turnstile siteverify returns success false', function () {
    Http::fake([
        'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => false,
            'error-codes' => ['invalid-input-response'],
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('turnstile')->verify('bad-token');

    expect($response->failed())->toBeTrue();
});
