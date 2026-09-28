<?php

namespace Lacaptcha\Lacaptcha\Tests;

use Lacaptcha\Lacaptcha\CaptchaServiceProvider;
use Lacaptcha\Lacaptcha\Facades\Captcha;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CaptchaServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Captcha' => Captcha::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('captcha.default', 'hcaptcha');

        $app['config']->set('captcha.drivers.hcaptcha', [
            'site_key' => 'hcaptcha-site-key',
            'secret_key' => 'hcaptcha-secret-key',
            'options' => ['theme' => 'light', 'size' => 'normal'],
        ]);

        $app['config']->set('captcha.drivers.recaptcha', [
            'version' => 'v2',
            'site_key' => 'recaptcha-site-key',
            'secret_key' => 'recaptcha-secret-key',
            'action' => 'submit',
            'min_score' => 0.5,
        ]);

        $app['config']->set('captcha.drivers.turnstile', [
            'site_key' => 'turnstile-site-key',
            'secret_key' => 'turnstile-secret-key',
            'options' => ['theme' => 'auto'],
        ]);

        $app['config']->set('captcha.drivers.null', [
            'succeeds' => true,
        ]);
    }
}
