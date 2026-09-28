<?php

namespace Lacaptcha\Lacaptcha\DataTransferObjects;

final readonly class CaptchaResponse
{
    /**
     * @param  array<int, string>  $errorCodes
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public bool $success,
        public array $errorCodes = [],
        public ?string $challengeTs = null,
        public ?string $hostname = null,
        public ?float $score = null,
        public ?string $action = null,
        public ?string $idempotencyKey = null,
        public array $raw = [],
    ) {}

    /**
     * Build a response from a provider's decoded siteverify JSON payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $errorCodes = $payload['error-codes'] ?? $payload['error_codes'] ?? [];

        return new self(
            success: (bool) ($payload['success'] ?? false),
            errorCodes: is_array($errorCodes) ? array_values($errorCodes) : [],
            challengeTs: $payload['challenge_ts'] ?? null,
            hostname: $payload['hostname'] ?? null,
            score: isset($payload['score']) ? (float) $payload['score'] : null,
            action: $payload['action'] ?? null,
            idempotencyKey: $payload['idempotency-key'] ?? $payload['idempotency_key'] ?? null,
            raw: $payload,
        );
    }

    public function failed(): bool
    {
        return ! $this->success;
    }

    /**
     * Return a copy of this response downgraded to a failure, preserving
     * every other field (e.g. score, idempotencyKey) instead of forcing
     * callers to reconstruct the DTO field-by-field and risk dropping one.
     */
    public function withFailure(string ...$errorCodes): self
    {
        return new self(
            success: false,
            errorCodes: [...$this->errorCodes, ...$errorCodes],
            challengeTs: $this->challengeTs,
            hostname: $this->hostname,
            score: $this->score,
            action: $this->action,
            idempotencyKey: $this->idempotencyKey,
            raw: $this->raw,
        );
    }
}
