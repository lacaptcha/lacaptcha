@once
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endonce

<div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-theme="{{ $theme }}"></div>

@include('lacaptcha::_error', ['field' => $responseField, 'show' => $showErrors])
