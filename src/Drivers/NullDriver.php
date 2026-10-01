<?php

namespace Lacaptcha\Lacaptcha\Drivers;

use Lacaptcha\Lacaptcha\Contracts\Driver;
use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

/**
 * Always resolves without making an HTTP call. Useful for local/testing
 * environments (CAPTCHA_DRIVER=null), and serves as the reference example
 * for adding a new driver: it only touches this class, its factory method
 * on the manager, its config block, and its widget view.
 */
class NullDriver implements Driver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function verify(string $token, ?string $remoteIp = null): CaptchaResponse
    {
        return CaptchaResponse::fromArray([
            'success' => (bool) ($this->config['succeeds'] ?? true),
        ]);
    }

    public function view(): string
    {
        return 'lacaptcha::null.widget';
    }

    public function widgetData(array $options = []): array
    {
        return $options;
    }

    public function name(): string
    {
        return 'null';
    }

    public function responseField(): string
    {
        return 'null-response';
    }
}
