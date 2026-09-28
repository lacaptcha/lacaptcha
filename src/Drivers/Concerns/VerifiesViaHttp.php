<?php

namespace Lacaptcha\Lacaptcha\Drivers\Concerns;

use Illuminate\Http\Client\ConnectionException;
use Lacaptcha\Lacaptcha\DataTransferObjects\CaptchaResponse;

/**
 * Shared by drivers whose $http (Illuminate\Http\Client\Factory) and $config
 * (array) properties back a standard "POST secret + response [+ remoteip],
 * get back JSON" siteverify-style endpoint.
 */
trait VerifiesViaHttp
{
    /**
     * Verify a token against the given siteverify-style URL and return the
     * mapped response. Centralizes the request shape shared by hCaptcha,
     * reCAPTCHA, and Turnstile so it only needs fixing in one place.
     */
    protected function verifyToken(string $url, string $token, ?string $remoteIp = null): CaptchaResponse
    {
        $payload = $this->postVerify($url, array_filter([
            'secret' => $this->config['secret_key'] ?? null,
            'response' => $token,
            'remoteip' => $remoteIp,
        ]));

        return CaptchaResponse::fromArray($payload);
    }

    /**
     * POST the given form params to a siteverify-style endpoint and return
     * the decoded JSON payload, defensively handling non-JSON (or
     * JSON-but-not-array, e.g. a WAF/maintenance page returning a bare
     * scalar) responses and connection failures so neither ever bubbles out
     * of a validation rule as an uncaught exception.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function postVerify(string $url, array $params): array
    {
        try {
            $response = $this->http->asForm()->post($url, $params);
        } catch (ConnectionException) {
            return ['success' => false, 'error-codes' => ['connection-failed']];
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : ['success' => false, 'error-codes' => ['invalid-response']];
    }
}
