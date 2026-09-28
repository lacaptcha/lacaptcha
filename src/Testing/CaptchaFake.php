<?php

namespace Lacaptcha\Lacaptcha\Testing;

use Illuminate\Contracts\Foundation\Application;
use Lacaptcha\Lacaptcha\CaptchaManager;
use Lacaptcha\Lacaptcha\Contracts\Driver;
use PHPUnit\Framework\Assert as PHPUnit;
use Throwable;

use function Illuminate\Support\enum_value;

class CaptchaFake extends CaptchaManager
{
    /** @var array<string, array{succeeds: bool, errorCodes: array<int, string>}> */
    private array $overrides = [];

    /** @var array<int, array{driver: string, token: string}> */
    private array $verifications = [];

    /**
     * @param  array<int, string>  $errorCodes
     */
    public function __construct(
        Application $app,
        private readonly ?string $driver = null,
        private readonly bool $succeeds = true,
        private readonly array $errorCodes = [],
    ) {
        parent::__construct($app);
    }

    /**
     * Configure a per-driver verification outcome, independent of the
     * outcome the fake was constructed with.
     *
     * @param  array<int, string>  $errorCodes
     */
    public function forDriver(string $driver, bool $succeeds = true, array $errorCodes = []): static
    {
        $this->overrides[$driver] = ['succeeds' => $succeeds, 'errorCodes' => $errorCodes];

        return $this;
    }

    public function driver($driver = null): Driver
    {
        // Mirror Illuminate\Support\Manager::driver()'s falsy fallback (not
        // just null) so driver('') behaves the same under the fake as it
        // does against the real manager.
        $name = enum_value($driver) ?: ($this->driver ?: $this->getDefaultDriver());

        $outcome = $this->overrides[$name] ?? ['succeeds' => $this->succeeds, 'errorCodes' => $this->errorCodes];

        return new FakeDriver(
            driverName: $name,
            succeeds: $outcome['succeeds'],
            errorCodes: $outcome['errorCodes'],
            recorder: function (string $name, string $token): void {
                $this->verifications[] = ['driver' => $name, 'token' => $token];
            },
            realDriver: $this->resolveRealDriver($name),
        );
    }

    /**
     * Resolve the real driver for view/widgetData delegation only, so Blade
     * rendering assertions still work while under a fake. Never invoked for
     * verification (verify() always stays on FakeDriver).
     */
    private function resolveRealDriver(string $name): ?Driver
    {
        try {
            return parent::createDriver($name);
        } catch (Throwable) {
            return null;
        }
    }

    public function assertVerified(?string $driver = null): void
    {
        $matches = array_filter(
            $this->verifications,
            fn (array $v) => $driver === null || $v['driver'] === $driver,
        );

        PHPUnit::assertNotEmpty(
            $matches,
            'No captcha verification was performed'.($driver ? " for driver [{$driver}]." : '.'),
        );
    }

    public function assertVerificationCount(int $count, ?string $driver = null): void
    {
        $matches = array_filter(
            $this->verifications,
            fn (array $v) => $driver === null || $v['driver'] === $driver,
        );

        PHPUnit::assertCount($count, $matches);
    }

    public function assertNothingVerified(): void
    {
        PHPUnit::assertEmpty($this->verifications, 'Unexpected captcha verification(s) were performed.');
    }
}
