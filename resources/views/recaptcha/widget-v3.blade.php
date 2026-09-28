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

        function execute() {
            grecaptcha.ready(function () {
                grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                    field.value = token;
                });
            });
        }

        if (form) {
            form.addEventListener('submit', execute);
        }

        execute();
    })();
</script>
