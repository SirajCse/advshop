<?php

namespace Botble\Ecommerce\Tables;

use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Table\Columns\FormattedColumn;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ProductInventoryTable extends ProductBulkEditableTable
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->addColumns([
                FormattedColumn::make('with_storehouse_management')
                    ->title(trans('plugins/ecommerce::product-inventory.storehouse_management'))
                    ->renderUsing(function (FormattedColumn $column) {
                        return view('plugins/ecommerce::product-inventory.columns.storehouse_management', [
                            'product' => $column->getItem(),
                            'type' => 'storehouse_management',
                        ]);
                    })
                    ->width(70)
                    ->nowrap()
                    ->orderable(false),
                FormattedColumn::make('quantity')
                    ->title(trans('plugins/ecommerce::products.form.quantity'))
                    ->renderUsing(function (FormattedColumn $column) {
                        return view('plugins/ecommerce::product-inventory.columns.quantity', [
                            'product' => $column->getItem(),
                        ]);
                    })
                    ->nowrap()
                    ->orderable(false),
            ]);
    }

    public function query()
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = parent::query();

        $query->addSelect([
            'ec_products.stock_status',
            'ec_products.quantity',
            'ec_products.with_storehouse_management',
            'ec_products.allow_checkout_when_out_of_stock',
        ]);

        return $query;
    }

    public function getFilters(): array
    {
        return [
            'stock_status' => [
                'title' => trans('plugins/ecommerce::products.stock_status'),
                'type' => 'select',
                'choices' => StockStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', StockStatusEnum::values()),
            ],
        ];
    }

    public function applyFilterCondition(
        EloquentBuilder|QueryBuilder|EloquentRelation $query,
        string $key,
        string $operator,
        ?string $value
    ): EloquentRelation|EloquentBuilder|QueryBuilder {
        if ($key === 'stock_status' && $value) {
            if ($value == StockStatusEnum::ON_BACKORDER) {
                return $query->where('ec_products.stock_status', StockStatusEnum::ON_BACKORDER);
            }

            if ($value == StockStatusEnum::OUT_OF_STOCK) {
                return $query
                    ->where(function ($query): void {
                        $query
                            ->where(function ($subQuery): void {
                                $subQuery
                                    ->where('ec_products.with_storehouse_management', 0)
                                    ->where('ec_products.stock_status', StockStatusEnum::OUT_OF_STOCK);
                            })
                            ->orWhere(function ($subQuery): void {
                                $subQuery
                                    ->where('ec_products.with_storehouse_management', 1)
                                    ->where('ec_products.allow_checkout_when_out_of_stock', 0)
                                    ->where('ec_products.quantity', '<=', 0);
                            });
                    });
            }

            if ($value == StockStatusEnum::IN_STOCK) {
                return $query
                    ->where(function ($query) {
                        return $query
                            ->where(function ($subQuery): void {
                                $subQuery
                                    ->where('ec_products.with_storehouse_management', 0)
                                    ->where('ec_products.stock_status', StockStatusEnum::IN_STOCK);
                            })
                            ->orWhere(function ($subQuery): void {
                                $subQuery
                                    ->where('ec_products.with_storehouse_management', 1)
                                    ->where(function ($sub): void {
                                        $sub
                                            ->where('ec_products.allow_checkout_when_out_of_stock', 1)
                                            ->orWhere('ec_products.quantity', '>', 0);
                                    });
                            });
                    });
            }
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }
}
