@php
    Theme::set('breadcrumbHeight', 120);
    Theme::set('breadcrumbClasses', 'pb-30');
    Theme::set('pageTitle', __('Forgot Password'));
@endphp

{!! $form->renderForm() !!}
