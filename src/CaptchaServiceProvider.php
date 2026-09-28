<?php

namespace Lacaptcha\Lacaptcha;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Lacaptcha\Lacaptcha\View\Components\Captcha;

class CaptchaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/captcha.php', 'captcha');

        $this->app->singleton(CaptchaManager::class, fn ($app) => new CaptchaManager($app));
        $this->app->alias(CaptchaManager::class, 'captcha');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lacaptcha');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lacaptcha');

        Blade::component(Captcha::class, 'captcha');

        Blade::directive('captcha', function (string $expression) {
            return "<?php echo app(\Lacaptcha\Lacaptcha\CaptchaManager::class)->widget({$expression})->render(); ?>";
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/captcha.php' => config_path('captcha.php'),
            ], 'lacaptcha-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/lacaptcha'),
            ], 'lacaptcha-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/lacaptcha'),
            ], 'lacaptcha-lang');
        }
    }
}
