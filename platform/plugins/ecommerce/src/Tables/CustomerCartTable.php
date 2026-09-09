<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Cart;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class CustomerCartTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Cart::class)
            ->addActions([
                DeleteAction::make()
                    ->url(fn (Action $action) => route('ecommerce.customer-carts.destroy', [
                        'identifier' => $action->getItem()->identifier,
                        'instance' => $action->getItem()->instance,
                    ]))
                    ->permission('ecommerce.customer-carts.destroy'),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->with('customer:id,name,email')
                    ->select([
                        'identifier',
                        'instance',
                        'content',
                        'customer_id',
                        'created_at',
                        'updated_at',
                    ])
                    ->latest('updated_at');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('identifier')
                ->title(trans('plugins/ecommerce::cart.identifier'))
                ->width(150)
                ->orderable(true),

            FormattedColumn::make('customer_id')
                ->title(trans('plugins/ecommerce::cart.customer'))
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    if (! $item->customer_id) {
                        return Html::tag('span', trans('plugins/ecommerce::cart.guest'), [
                            'class' => 'badge bg-secondary-lt',
                        ]);
                    }

                    if ($item->customer) {
                        return Html::link(
                            route('customers.edit', $item->customer_id),
                            $item->customer->name,
                            ['title' => $item->customer->email]
                        );
                    }

                    return '-';
                }),

            Column::make('instance')
                ->title(trans('plugins/ecommerce::cart.instance'))
                ->width(100)
                ->orderable(true),

            FormattedColumn::make('content')
                ->title(trans('plugins/ecommerce::cart.items'))
                ->width(100)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    return Html::tag('span', (string) $item->item_count, [
                        'class' => 'badge bg-blue-lt',
                    ]);
                }),

            FormattedColumn::make('total')
                ->title(trans('plugins/ecommerce::cart.total'))
                ->width(120)
                ->searchable(false)
                ->orderable(false)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    return format_price($item->raw_total);
                }),

            CreatedAtColumn::make('updated_at')
                ->title(trans('core/base::tables.updated_at'))
                ->dateFormat('Y-m-d H:i'),
        ];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [
            'customer_id' => [
                'title' => trans('plugins/ecommerce::cart.customer'),
                'type' => 'select-ajax',
                'url' => route('customers.get-list-customers-for-select'),
                'validate' => 'nullable|integer',
            ],
            'instance' => [
                'title' => trans('plugins/ecommerce::cart.instance'),
                'type' => 'select',
                'choices' => [
                    'cart' => trans('plugins/ecommerce::cart.cart'),
                    'wishlist' => trans('plugins/ecommerce::cart.wishlist'),
                ],
                'validate' => ['nullable', Rule::in(['cart', 'wishlist'])],
            ],
            'is_guest' => [
                'title' => trans('plugins/ecommerce::cart.is_guest'),
                'type' => 'select',
                'choices' => [
                    '1' => trans('core/base::base.yes'),
                    '0' => trans('core/base::base.no'),
                ],
                'validate' => 'nullable|in:0,1',
            ],
        ];
    }

    public function applyFilterCondition(
        $query,
        string $key,
        string $operator,
        ?string $value
    ) {
        if ($key === 'is_guest') {
            return $value === '1'
                ? $query->whereNull('customer_id')
                : $query->whereNotNull('customer_id');
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }

    public function getDefaultButtons(): array
    {
        return ['reload'];
    }
}
