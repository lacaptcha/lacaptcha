<?php

namespace Lacaptcha\Lacaptcha\Contracts;

use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

interface Driver
{
    /**
     * Verify a submitted captcha token against the provider's API.
     */
    public function verify(string $token, ?string $remoteIp = null): CaptchaResponse;

    /**
     * The dot-notation Blade view used to render this driver's widget.
     */
    public function view(): string;

    /**
     * Data passed into the widget view, merged with any per-call overrides.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function widgetData(array $options = []): array;

    /**
     * The driver's registered name (e.g. "hcaptcha").
     */
    public function name(): string;
}
