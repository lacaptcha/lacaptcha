<?php

use Illuminate\Support\Facades\Http;
use Lacaptcha\Lacaptcha\CaptchaManager;

beforeEach(function () {
    config()->set('captcha.drivers.recaptcha', [
        'version' => 'v3',
        'site_key' => 'recaptcha-site-key',
        'secret_key' => 'recaptcha-secret-key',
        'action' => 'login',
        'min_score' => 0.5,
    ]);
});

it('succeeds when the score is above the minimum and the action matches', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.9,
            'action' => 'login',
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('recaptcha')->verify('token');

    expect($response->success)->toBeTrue();
});

it('fails when the score is below the configured minimum', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.1,
            'action' => 'login',
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('recaptcha')->verify('token');

    expect($response->failed())->toBeTrue()
        ->and($response->errorCodes)->toContain('score-or-action-mismatch');
});

it('fails when the action does not match the configured expectation', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.9,
            'action' => 'signup',
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('recaptcha')->verify('token');

    expect($response->failed())->toBeTrue();
});

it('respects a changed min_score value from config', function () {
    config()->set('captcha.drivers.recaptcha.min_score', 0.95);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.9,
            'action' => 'login',
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('recaptcha')->verify('token');

    expect($response->failed())->toBeTrue();
});

it('preserves the idempotency key when downgrading to a failure', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.1,
            'action' => 'login',
            'idempotency-key' => 'idem-1',
        ]),
    ]);

    $response = app(CaptchaManager::class)->driver('recaptcha')->verify('token');

    expect($response->failed())->toBeTrue()
        ->and($response->idempotencyKey)->toBe('idem-1');
});
