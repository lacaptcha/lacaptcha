<?php

namespace Lacaptcha\Lacaptcha\Drivers;

use Illuminate\Http\Client\Factory;
use Lacaptcha\Lacaptcha\Contracts\Driver;
use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

class HcaptchaDriver implements Driver
{
    use Concerns\VerifiesViaHttp;

    private const VERIFY_URL = 'https://hcaptcha.com/siteverify';

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
        return 'lacaptcha::hcaptcha.widget';
    }

    public function widgetData(array $options = []): array
    {
        return array_merge([
            'siteKey' => $this->config['site_key'] ?? null,
            'theme' => $this->config['options']['theme'] ?? 'light',
            'size' => $this->config['options']['size'] ?? 'normal',
        ], $options);
    }

    public function name(): string
    {
        return 'hcaptcha';
    }
}
