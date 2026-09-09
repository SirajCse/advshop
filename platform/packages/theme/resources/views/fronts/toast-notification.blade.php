@once
    @php
        $successIcon = theme_option('toast_success_icon');
        $errorIcon = theme_option('toast_error_icon');
    @endphp
    <script>
        window.ThemeToastConfig = {
            position: @json(theme_option('toast_position', 'bottom')),
            alignment: @json(theme_option('toast_alignment', 'right')),
            offsetX: @json((int) theme_option('toast_offset_x', 15)),
            offsetY: @json((int) theme_option('toast_offset_y', 15)),
            timeout: @json((int) theme_option('toast_timeout', 5000)),
            successIcon: @json($successIcon ? BaseHelper::renderIcon($successIcon) : ''),
            errorIcon: @json($errorIcon ? BaseHelper::renderIcon($errorIcon) : '')
        };
    </script>
    <script src="{{ asset('vendor/core/packages/theme/js/toast.js') }}?v={{ get_cms_version() }}"></script>

    @if (session()->has('success_msg') ||
            session()->has('error_msg') ||
            (isset($errors) && $errors->count() > 0) ||
            isset($error_msg))
        <script type="text/javascript">
            window.addEventListener('load', function() {
                @if (session()->has('success_msg'))
                Theme.showSuccess(@json(session('success_msg')));
                @endif

                @if (session()->has('error_msg'))
                Theme.showError(@json(session('error_msg')));
                @endif

                @if (isset($error_msg))
                Theme.showError(@json($error_msg));
                @endif

                @if (isset($errors))
                @foreach ($errors->all() as $error)
                Theme.showError(@json($error));
                @endforeach
                @endif
            });
        </script>
    @endif
@endonce
