<?php

namespace Lacaptcha\Lacaptcha\Drivers;

use Illuminate\Http\Client\Factory;
use Lacaptcha\Lacaptcha\Contracts\Driver;
use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

class TurnstileDriver implements Driver
{
    use Concerns\VerifiesViaHttp;

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly Factory $http,
        private readonly array $config,
    ) {}

    public function verify(string $token, ?string $remoteIp = null): CaptchaResponse
    {
        return $this->verifyToken(self::VERIFY_URL, $token, $remoteIp);
    }

    public function view(): string
    {
        return 'lacaptcha::turnstile.widget';
    }

    public function widgetData(array $options = []): array
    {
        return array_merge([
            'siteKey' => $this->config['site_key'] ?? null,
            'theme' => $this->config['options']['theme'] ?? 'auto',
        ], $options);
    }

    public function name(): string
    {
        return 'turnstile';
    }

    public function responseField(): string
    {
        return 'cf-turnstile-response';
    }
}
