@once
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
@endonce

<div class="h-captcha" data-sitekey="{{ $siteKey }}" data-theme="{{ $theme }}" data-size="{{ $size }}"></div>

@include('lacaptcha::_error', ['field' => $responseField, 'show' => $showErrors])
