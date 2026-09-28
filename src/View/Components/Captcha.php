<?php

namespace Lacaptcha\Lacaptcha\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Lacaptcha\Lacaptcha\CaptchaManager;

class Captcha extends Component
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        private readonly CaptchaManager $manager,
        public ?string $driver = null,
        public array $options = [],
    ) {}

    public function render(): View
    {
        return $this->manager->widget($this->driver, $this->options);
    }
}
