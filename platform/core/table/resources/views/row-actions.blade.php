@php
    /** @var \Botble\Table\Abstracts\TableAbstract $table */
    /** @var \Botble\Table\Abstracts\TableActionAbstract[] $actions */
    /** @var \Illuminate\Database\Eloquent\Model $model */

    $renderedActions = collect($actions)
        ->map(fn ($action) => ['action' => $action, 'html' => (string) $action->setItem($model)])
        ->filter(fn ($item) => $item['html'] !== '');

    $visibleCount = $renderedActions->count();
    $showAsDropdown = $table->hasDisplayActionsAsDropdown()
        && $visibleCount > $table->getDisplayActionsAsDropdownWhenActionsMoresThan();
@endphp

<div class="table-actions">
    @if (!$showAsDropdown)
        @foreach ($renderedActions as $item)
            {!! $item['html'] !!}
        @endforeach
    @else
        <div class="dropdown">
            <button
                class="btn dropdown-toggle"
                type="button"
                id="{{ $id = sprintf('dropdown-actions-%s-%s', md5($model::class), $model->getKey()) }}"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                {{ trans('core/base::tables.action') }}
            </button>
            <div
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="{{ $id }}"
            >
                @foreach ($renderedActions as $item)
                    {{ $item['action']->setItem($model)->displayAsDropdownItem() }}
                @endforeach
            </div>
        </div>
    @endif
</div>
