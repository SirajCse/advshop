<?php

namespace Botble\Ecommerce\Tables\BulkChanges;

use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Table\BulkChanges\SelectBulkChange;
use Illuminate\Validation\Rule;

class StockStatusBulkChange extends SelectBulkChange
{
    public static function make(array $data = []): static
    {
        return parent::make()
            ->name('stock_status')
            ->title(trans('plugins/ecommerce::products.stock_status'))
            ->choices(StockStatusEnum::labels())
            ->validate(['required', Rule::in(StockStatusEnum::values())]);
    }
}
