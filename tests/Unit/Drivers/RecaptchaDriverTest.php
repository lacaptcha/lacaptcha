<?php

use Illuminate\Support\Facades\Http;
use Lacaptcha\Lacaptcha\CaptchaManager;

it('posts to recaptcha siteverify and succeeds for v2', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
    ]);

    $response = app(CaptchaManager::class)->driver('recaptcha')->verify('token-xyz', '9.9.9.9');

    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://www.google.com/recaptcha/api/siteverify'
            && $request['secret'] === 'recaptcha-secret-key'
            && $request['response'] === 'token-xyz'
            && $request['remoteip'] === '9.9.9.9';
    });
});

it('fails when recaptcha siteverify returns success false', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => false,
            'error-codes' => ['invalid-input-response'],
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('recaptcha')->verify('bad-token');

    expect($response->failed())->toBeTrue();
});
