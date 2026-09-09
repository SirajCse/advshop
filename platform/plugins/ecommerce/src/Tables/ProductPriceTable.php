<?php

namespace Botble\Ecommerce\Tables;

use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Table\Columns\FormattedColumn;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ProductPriceTable extends ProductBulkEditableTable
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setView('plugins/ecommerce::product-prices.index')
            ->addColumns([
                FormattedColumn::make('cost_per_item')
                    ->title(trans('plugins/ecommerce::products.form.cost_per_item'))
                    ->renderUsing(function (FormattedColumn $column) {
                        return view('plugins/ecommerce::product-prices.columns.price', [
                            'product' => $column->getItem(),
                            'type' => 'cost_per_item',
                        ]);
                    })
                    ->nowrap()
                    ->width(150)
                    ->orderable(false),
                FormattedColumn::make('price')
                    ->title(trans('plugins/ecommerce::products.form.price'))
                    ->renderUsing(function (FormattedColumn $column) {
                        return view('plugins/ecommerce::product-prices.columns.price', [
                            'product' => $column->getItem(),
                            'type' => 'price',
                        ]);
                    })
                    ->nowrap()
                    ->width(150)
                    ->orderable(false),
                FormattedColumn::make('sale_price')
                    ->title(trans('plugins/ecommerce::products.form.price_sale'))
                    ->renderUsing(function (FormattedColumn $column) {
                        return view('plugins/ecommerce::product-prices.columns.price', [
                            'product' => $column->getItem(),
                            'type' => 'sale_price',
                        ]);
                    })
                    ->nowrap()
                    ->width(150)
                    ->orderable(false),
            ]);
    }

    public function query()
    {
        /**
         * @var Builder $query
         */
        $query = parent::query();

        $query->addSelect([
            'ec_products.cost_per_item',
            'ec_products.price',
            'ec_products.sale_price',
            'ec_products.sale_type',
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
