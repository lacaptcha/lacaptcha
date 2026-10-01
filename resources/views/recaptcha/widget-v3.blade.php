@once
    <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}"></script>
@endonce

<input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response">

<script>
    (function () {
        var siteKey = @js($siteKey);
        var action = @js($action);
        var field = document.currentScript.previousElementSibling;
        var form = field.closest('form');

        function refreshToken() {
            return new Promise(function (resolve) {
                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                        field.value = token;
                        resolve(token);
                    });
                });
            });
        }

        if (form) {
            // v3 tokens are short-lived, so a fresh one is fetched at submit
            // time; the native submit is held back with preventDefault()
            // until that token is in place, then re-triggered via
            // form.submit() (which does not re-fire the "submit" event, so
            // this does not recurse).
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                refreshToken().then(function () {
                    form.submit();
                });
            });
        }

        refreshToken();
    })();
</script>
