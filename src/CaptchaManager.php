<?php

namespace Lacaptcha\Lacaptcha;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use Lacaptcha\Lacaptcha\Contracts\Driver;
use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;
use Lacaptcha\Lacaptcha\Drivers\HcaptchaDriver;
use Lacaptcha\Lacaptcha\Drivers\NullDriver;
use Lacaptcha\Lacaptcha\Drivers\RecaptchaDriver;
use Lacaptcha\Lacaptcha\Drivers\TurnstileDriver;
use Lacaptcha\Lacaptcha\Exceptions\NullDriverNotAllowedException;
use Lacaptcha\Lacaptcha\Exceptions\UnsupportedDriverException;

use function Illuminate\Support\enum_value;

class CaptchaManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('captcha.default', 'hcaptcha');
    }

    /**
     * Convenience shortcut for verifying against the resolved driver.
     */
    public function verify(string $token, ?string $remoteIp = null): CaptchaResponse
    {
        return $this->driver()->verify($token, $remoteIp);
    }

    /**
     * Resolve a driver by name and turn it into its renderable widget view.
     * The single source of truth used by both the <x-captcha /> component
     * and the @captcha Blade directive.
     *
     * A validation error for the driver's response field is shown
     * automatically beneath the widget, when one is present; pass
     * `showErrors: false` in $options to turn that off and handle error
     * display yourself (see Driver::responseField() / responseField()).
     *
     * @param  array<string, mixed>  $options
     */
    public function widget(?string $driver = null, array $options = []): View
    {
        $driver = $this->driver($driver);
        $showErrors = (bool) ($options['showErrors'] ?? true);
        unset($options['showErrors'], $options['responseField']);

        return view('lacaptcha::_widget', [
            'view' => $driver->view(),
            'data' => $driver->widgetData($options),
            'responseField' => $driver->responseField(),
            'showErrors' => $showErrors,
        ]);
    }

    /**
     * The form field name the given (or default) driver's widget populates,
     * so a validation rule and its matching error-display markup can agree
     * on the same field without hardcoding each provider's own naming.
     */
    public function responseField(?string $driver = null): string
    {
        return $this->driver($driver)->responseField();
    }

    protected function createHcaptchaDriver(): Driver
    {
        return new HcaptchaDriver(
            $this->container->make(Factory::class),
            $this->config->get('captcha.drivers.hcaptcha', []),
        );
    }

    protected function createRecaptchaDriver(): Driver
    {
        return new RecaptchaDriver(
            $this->container->make(Factory::class),
            $this->config->get('captcha.drivers.recaptcha', []),
        );
    }

    protected function createTurnstileDriver(): Driver
    {
        return new TurnstileDriver(
            $this->container->make(Factory::class),
            $this->config->get('captcha.drivers.turnstile', []),
        );
    }

    /**
     * The null driver always passes verification, so it is refused outside
     * local/testing unless explicitly opted into via config — this stops a
     * stray or copy-pasted CAPTCHA_DRIVER=null from silently disabling
     * captcha protection in production.
     */
    protected function createNullDriver(): Driver
    {
        $config = $this->config->get('captcha.drivers.null', []);

        if (! $this->container->environment('local', 'testing') && ! ($config['allow_in_production'] ?? false)) {
            throw NullDriverNotAllowedException::make();
        }

        return new NullDriver($config);
    }

    /**
     * Only intercept the "no such driver" case with a friendlier exception;
     * an InvalidArgumentException thrown from inside a create*Driver()
     * factory itself (e.g. bad config) must propagate unchanged instead of
     * being caught here and misreported as an unsupported driver.
     */
    protected function createDriver($driver)
    {
        $name = (string) enum_value($driver);

        if (isset($this->customCreators[$name]) || method_exists($this, 'create'.Str::studly($name).'Driver')) {
            return parent::createDriver($driver);
        }

        throw UnsupportedDriverException::make($name);
    }
}
