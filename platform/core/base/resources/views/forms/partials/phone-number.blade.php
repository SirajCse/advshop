{!! Form::text(
    $name,
    $value,
    array_merge_recursive($attributes, ['class' => 'js-phone-number-mask form-control']),
) !!}
