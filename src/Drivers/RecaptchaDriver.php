<?php

namespace Lacaptcha\Lacaptcha\Drivers;

use Illuminate\Http\Client\Factory;
use Lacaptcha\Lacaptcha\Contracts\Driver;
use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

class RecaptchaDriver implements Driver
{
    use Concerns\VerifiesViaHttp;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly Factory $http,
        private readonly array $config,
    ) {}

    public function verify(string $token, ?string $remoteIp = null): CaptchaResponse
    {
        $result = $this->verifyToken(self::VERIFY_URL, $token, $remoteIp);

        return $this->isV3() ? $this->applyV3Policy($result) : $result;
    }

    private function isV3(): bool
    {
        return ($this->config['version'] ?? 'v2') === 'v3';
    }

    /**
     * reCAPTCHA v3 always returns success=true for a structurally valid
     * token; the score/action checks that actually decide pass/fail are the
     * consumer's responsibility. Apply the configured minimum score and
     * expected action here so a "technically successful" but low-score or
     * mismatched-action response is treated as a failed verification.
     */
    private function applyV3Policy(CaptchaResponse $result): CaptchaResponse
    {
        if (! $result->success) {
            return $result;
        }

        $minScore = $this->config['min_score'] ?? 0.5;
        $expectedAction = $this->config['action'] ?? null;

        $passesScore = $result->score === null || $result->score >= $minScore;
        $passesAction = $expectedAction === null || $result->action === $expectedAction;

        if ($passesScore && $passesAction) {
            return $result;
        }

        return $result->withFailure('score-or-action-mismatch');
    }

    public function view(): string
    {
        return $this->isV3() ? 'lacaptcha::recaptcha.widget-v3' : 'lacaptcha::recaptcha.widget-v2';
    }

    public function widgetData(array $options = []): array
    {
        return array_merge([
            'siteKey' => $this->config['site_key'] ?? null,
            'action' => $this->config['action'] ?? 'submit',
        ], $options);
    }

    public function name(): string
    {
        return 'recaptcha';
    }

    public function responseField(): string
    {
        return 'g-recaptcha-response';
    }
}
