# lacaptcha/lacaptcha

[![Tests](https://github.com/lacaptcha/lacaptcha/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/lacaptcha/lacaptcha/actions/workflows/tests.yml)

Multi-provider captcha integration for Laravel. Drop a widget into any Blade form and validate it server-side, without coupling your app to a single captcha provider.

Supported drivers out of the box:

- **hCaptcha**
- **Google reCAPTCHA** (v2 checkbox and v3 score-based)
- **Cloudflare Turnstile**
- **Null** — always passes, no HTTP call, for local/dev/CI

## Installation

```bash
composer require lacaptcha/lacaptcha
```

The package auto-registers its service provider and `Captcha` facade via Laravel package discovery.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=lacaptcha-config
```

Set `CAPTCHA_DRIVER` to the provider you want active, and fill in that provider's keys:

```env
CAPTCHA_DRIVER=hcaptcha

HCAPTCHA_SITE_KEY=
HCAPTCHA_SECRET_KEY=

RECAPTCHA_VERSION=v2
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
RECAPTCHA_ACTION=submit
RECAPTCHA_MIN_SCORE=0.5

TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=

CAPTCHA_NULL_SUCCEEDS=true
```

> **Security note:** the `null` driver always passes verification. To stop a stray or copy-pasted
> `CAPTCHA_DRIVER=null` from silently disabling captcha protection in production, resolving it
> outside `local`/`testing` throws `NullDriverNotAllowedException` unless you explicitly set
> `CAPTCHA_NULL_ALLOW_IN_PRODUCTION=true`.

## Usage

### Blade

```blade
<form method="POST">
    @csrf
    <x-captcha />
    <button type="submit">Submit</button>
</form>
```

Override the driver per-call regardless of the configured default:

```blade
<x-captcha driver="turnstile" />
<x-captcha driver="recaptcha" :options="['action' => 'login']" />
```

Alternatively, using the `@captcha` Blade directive — it renders identically to `<x-captcha />` since both share the same underlying `CaptchaManager::widget()` method:

```blade
@captcha
@captcha('turnstile')
@captcha('recaptcha', ['action' => 'login'])
```

### Validation

```php
use Lacaptcha\Lacaptcha\Rules\Captcha;

$request->validate([
    'h-captcha-response' => ['required', new Captcha],
    // or, to force a specific provider regardless of the default:
    // 'cf-turnstile-response' => ['required', new Captcha('turnstile')],
]);
```

### Error handling

`<x-captcha />` and `@captcha` automatically show a validation error for their driver's
response field, if one is present — no manual `@error()` block needed:

```blade
<x-captcha :driver="$driver" />
@captcha($driver)
```

It renders as `<p class="lacaptcha-error">{{ message }}</p>` directly beneath the widget,
with no color/spacing of its own — style it in your own CSS via the `.lacaptcha-error`
class. Turn it off with `showErrors: false` to handle error display yourself:

```blade
<x-captcha :driver="$driver" :options="['showErrors' => false]" />
@captcha($driver, ['showErrors' => false])
```

Each driver's widget populates a provider-specific form field (`h-captcha-response`,
`g-recaptcha-response`, `cf-turnstile-response`, ...), which is also the field name a
validation failure attaches its error to — the widget already knows it via
`Driver::responseField()`. If your app supports switching drivers (so you can't hardcode
that field name in your own `$request->validate([...])` call), ask the package for it the
same way instead of duplicating the provider → field-name mapping yourself:

```php
use Lacaptcha\Lacaptcha\CaptchaManager;
use Lacaptcha\Lacaptcha\Rules\Captcha;

public function store(Request $request, CaptchaManager $captcha)
{
    $driver = $request->input('driver', config('captcha.default'));
    $field = $captcha->responseField($driver); // or ->responseField() for the default driver

    $request->validate([
        $field => ['required', new Captcha($driver)],
    ]);

    // ...
}
```

### Facade

```php
use Lacaptcha\Lacaptcha\Facades\Captcha;

Captcha::verify($token);                 // uses the default driver
Captcha::driver('turnstile')->verify($token);
Captcha::responseField('turnstile');     // 'cf-turnstile-response'
```

## Testing

Fake verification in your own feature tests so nothing hits a real provider:

```php
use Lacaptcha\Lacaptcha\Facades\Captcha;

Captcha::fake();                                   // always succeeds
Captcha::fake(succeeds: false);                     // always fails
Captcha::fake()->forDriver('turnstile', succeeds: false);

// ...run the code under test...

Captcha::fake()->assertVerified();
Captcha::fake()->assertVerificationCount(1);
Captcha::fake()->assertNothingVerified();
```

## Provider notes

| Field | hCaptcha | reCAPTCHA v2 | reCAPTCHA v3 | Turnstile |
|---|---|---|---|---|
| `success` | ✓ | ✓ | ✓ (structural only — see below) | ✓ |
| `challengeTs` / `hostname` | ✓ | ✓ | ✓ | ✓ |
| `score` / `action` | – | – | ✓ | `action` only |
| `idempotencyKey` | – | – | – | ✓ |

reCAPTCHA v3 always reports `success: true` for a structurally valid token — score and action are the consumer's responsibility to check. The `RecaptchaDriver` applies `captcha.drivers.recaptcha.min_score` and `captcha.drivers.recaptcha.action` itself, downgrading a low-score or mismatched-action response to a failed `CaptchaResponse`.

## Adding a new driver

No changes are needed to the Blade component, validation rule, facade, service provider
registration, or error display (handled centrally by `CaptchaManager::widget()` for every
driver) — only:

1. Implement `Lacaptcha\Lacaptcha\Contracts\Driver` in a new `Drivers/XDriver.php`.
2. Add a `createXDriver()` factory method to `CaptchaManager`.
3. Add a widget view, e.g. `resources/views/x/widget.blade.php`, containing only the
   provider's own markup/script.
4. Add a `drivers.x` block to `config/captcha.php`.

`Drivers/NullDriver.php` is a complete, working example of exactly this.

## License

MIT. See [LICENSE.md](LICENSE.md).
