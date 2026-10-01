{{--
    Deliberately not using @error()/@enderror here: that directive compiles
    to $errors->getBag(...) unconditionally, which throws if $errors was
    never shared into this render (e.g. a bare Blade::render() call, a
    console-rendered view, or any route outside the "web" middleware
    group's ShareErrorsFromSession). The isset() check keeps this view safe
    to render in any context, same as every other widget view in this
    package.
--}}
@if (($show ?? true) && isset($errors) && $errors->has($field))
    <p class="lacaptcha-error">{{ $errors->first($field) }}</p>
@endif
