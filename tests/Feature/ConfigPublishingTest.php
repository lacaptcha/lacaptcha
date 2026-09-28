<?php

use Illuminate\Support\Facades\Artisan;

it('publishes the config file', function () {
    Artisan::call('vendor:publish', ['--tag' => 'lacaptcha-config', '--force' => true]);

    $published = config_path('captcha.php');

    expect(file_exists($published))->toBeTrue();

    $config = require $published;

    expect($config)->toHaveKey('default')
        ->and($config['drivers'])->toHaveKeys(['hcaptcha', 'recaptcha', 'turnstile', 'null']);

    @unlink($published);
});
