<?php

use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

it('maps a hcaptcha-shaped payload', function () {
    $response = CaptchaResponse::fromArray([
        'success' => true,
        'challenge_ts' => '2026-01-01T00:00:00Z',
        'hostname' => 'example.test',
    ]);

    expect($response->success)->toBeTrue()
        ->and($response->failed())->toBeFalse()
        ->and($response->challengeTs)->toBe('2026-01-01T00:00:00Z')
        ->and($response->hostname)->toBe('example.test')
        ->and($response->errorCodes)->toBe([])
        ->and($response->score)->toBeNull();
});

it('maps a recaptcha v3-shaped payload', function () {
    $response = CaptchaResponse::fromArray([
        'success' => true,
        'score' => 0.9,
        'action' => 'submit',
    ]);

    expect($response->score)->toBe(0.9)
        ->and($response->action)->toBe('submit');
});

it('maps a turnstile-shaped payload with a hyphenated idempotency key', function () {
    $response = CaptchaResponse::fromArray([
        'success' => true,
        'idempotency-key' => 'abc-123',
    ]);

    expect($response->idempotencyKey)->toBe('abc-123');
});

it('defaults success to false and error codes to an empty array when missing', function () {
    $response = CaptchaResponse::fromArray([]);

    expect($response->success)->toBeFalse()
        ->and($response->failed())->toBeTrue()
        ->and($response->errorCodes)->toBe([]);
});
