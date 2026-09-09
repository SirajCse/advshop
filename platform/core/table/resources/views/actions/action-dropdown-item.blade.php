@php
    /** @var Botble\Table\Actions\Action $action */
    $attributes = collect($action->getAttributes())->except('class')->all();
@endphp

<li>
    <a
        @class([
            'dropdown-item',
            str_replace('btn-', 'text-', $action->getColor()),
        ])
        @include('core/table::actions.includes.action-attributes', ['attributes' => $attributes])
    >
        @include('core/table::actions.includes.action-icon')

        <span class="ms-1">{{ $action->getLabel() }}</span>
    </a>
</li>
