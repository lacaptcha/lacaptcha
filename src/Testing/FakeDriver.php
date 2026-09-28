<?php

namespace Lacaptcha\Lacaptcha\Testing;

use Closure;
use Lacaptcha\Lacaptcha\Contracts\Driver;
use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

class FakeDriver implements Driver
{
    /**
     * @param  array<int, string>  $errorCodes
     * @param  (Closure(string, string): void)|null  $recorder
     */
    public function __construct(
        private readonly string $driverName,
        private readonly bool $succeeds,
        private readonly array $errorCodes,
        private readonly ?Closure $recorder = null,
        private readonly ?Driver $realDriver = null,
    ) {}

    public function verify(string $token, ?string $remoteIp = null): CaptchaResponse
    {
        if ($this->recorder) {
            ($this->recorder)($this->driverName, $token);
        }

        return CaptchaResponse::fromArray([
            'success' => $this->succeeds,
            'error-codes' => $this->errorCodes,
        ]);
    }

    public function view(): string
    {
        return $this->realDriver?->view() ?? 'lacaptcha::null.widget';
    }

    public function widgetData(array $options = []): array
    {
        return $this->realDriver?->widgetData($options) ?? $options;
    }

    public function name(): string
    {
        return $this->driverName;
    }
}
