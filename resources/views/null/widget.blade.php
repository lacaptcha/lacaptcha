{{-- The null driver has no real widget; nothing is rendered beyond a possible error. --}}
@include('lacaptcha::_error', ['field' => $responseField, 'show' => $showErrors])
