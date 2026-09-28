<?php

namespace Lacaptcha\Lacaptcha\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Lacaptcha\Lacaptcha\CaptchaManager;

class Captcha implements ValidationRule
{
    public function __construct(
        private readonly ?string $driver = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var CaptchaManager $manager */
        $manager = app(CaptchaManager::class);

        $response = $manager->driver($this->driver)->verify(
            token: (string) $value,
            remoteIp: request()?->ip(),
        );

        if ($response->failed()) {
            $fail(__('lacaptcha::captcha.failed'));
        }
    }
}
