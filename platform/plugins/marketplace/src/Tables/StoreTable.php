<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Facades\Html;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Models\Store;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class StoreTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Store::class)
            ->addActions([
                ViewAction::make()
                    ->route('marketplace.store.view')
                    ->permission('marketplace.store.view'),
                EditAction::make()->route('marketplace.store.edit'),
                DeleteAction::make()->route('marketplace.store.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                $name = Html::link(route('marketplace.store.edit', $item->id), $item->name);
                if ($item->is_verified) {
                    $name .= ' ' . view('plugins/marketplace::partials.verified-badge', ['size' => 'sm'])->render();
                }

                if ($item->vacation_mode) {
                    $name .= ' ' . Html::tag(
                        'span',
                        trans('plugins/marketplace::store.forms.vacation_badge'),
                        ['class' => 'badge bg-orange text-orange-fg']
                    )->toHtml();
                }

                return $name;
            })
            ->editColumn('earnings', function ($item) {
                return format_price((float) $item->store_earnings);
            })
            ->editColumn('products_count', function ($item) {
                return $item->products_count;
            })
            ->addColumn('customer_name', function ($item) {
                if (! $item->customer->name) {
                    return '&mdash;';
                }

                return Html::link(route('customers.edit', $item->customer->id), $item->customer->name);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $earningsSubQuery = 'COALESCE((SELECT SUM(CASE '
            . 'WHEN mp_customer_revenues.type IS NULL OR mp_customer_revenues.type = ? THEN mp_customer_revenues.amount '
            . 'WHEN mp_customer_revenues.type = ? THEN mp_customer_revenues.amount * -1 '
            . 'ELSE 0 END) '
            . 'FROM mp_customer_revenues '
            . 'INNER JOIN ec_orders ON ec_orders.id = mp_customer_revenues.order_id '
            . 'WHERE ec_orders.store_id = mp_stores.id AND ec_orders.is_finished = 1), 0) AS store_earnings';

        $query = $this
            ->getModel()
            ->query()
            ->select([
                'mp_stores.id',
                'mp_stores.logo',
                'mp_stores.name',
                'mp_stores.created_at',
                'mp_stores.status',
                'mp_stores.customer_id',
                'mp_stores.is_verified',
                'mp_stores.vacation_mode',
            ])
            ->selectRaw($earningsSubQuery, [
                RevenueTypeEnum::ADD_AMOUNT,
                RevenueTypeEnum::SUBTRACT_AMOUNT,
            ])
            ->with(['customer', 'customer.vendorInfo'])
            ->withCount(['products']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make('logo')
                ->title(trans('plugins/marketplace::store.forms.logo')),
            NameColumn::make()->route('marketplace.store.edit'),
            Column::make('earnings')
                ->title(trans('plugins/marketplace::marketplace.tables.earnings'))
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->width('100'),
            Column::make('products_count')
                ->title(trans('plugins/marketplace::marketplace.tables.products_count'))
                ->orderable(false)
                ->searchable(false),
            Column::make('customer_name')
                ->title(trans('plugins/marketplace::marketplace.vendor'))
                ->alignStart()
                ->orderable(false)
                ->searchable(false)
                ->printable(false),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.store.create'), 'marketplace.store.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('marketplace.store.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => StoreStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', StoreStatusEnum::values()),
            ],
            'is_verified' => [
                'title' => trans('plugins/marketplace::store.forms.is_verified'),
                'type' => 'select',
                'choices' => [
                    0 => trans('core/base::base.no'),
                    1 => trans('core/base::base.yes'),
                ],
                'validate' => 'required|in:0,1',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
