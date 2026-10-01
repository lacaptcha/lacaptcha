@once
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endonce

<div class="g-recaptcha" data-sitekey="{{ $siteKey }}"></div>

@include('lacaptcha::_error', ['field' => $responseField, 'show' => $showErrors])
