{{--
    The single place that wires a driver's own widget view together with
    the shared error partial, so every driver gets automatic error display
    for free — no driver's widget view needs to know about it, and a new
    driver can't forget to include it.
--}}
@include($view, $data)
@include('lacaptcha::_error', ['field' => $responseField, 'show' => $showErrors])
