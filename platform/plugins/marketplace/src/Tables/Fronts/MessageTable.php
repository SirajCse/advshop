<?php

namespace Botble\Marketplace\Tables\Fronts;

use Botble\Marketplace\Models\Message;
use Botble\Marketplace\Tables\Traits\ForVendor;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EmailColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Builder;

class MessageTable extends TableAbstract
{
    use ForVendor;

    public function setup(): void
    {
        $this
            ->model(Message::class)
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('marketplace.vendor.messages.show'),
                EmailColumn::make()->linkable(),
                FormattedColumn::make('content')->limit(50)->label(trans('plugins/marketplace::store.forms.content')),
                CreatedAtColumn::make(),
            ])
            ->addActions([
                ViewAction::make()
                    ->url(fn (Action $action) => route('marketplace.vendor.messages.show', $action->getItem())),
                DeleteAction::make()
                    ->url(fn (Action $action) => route('marketplace.vendor.messages.destroy', $action->getItem())),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->queryUsing(function (Builder $query) {
                return $query->where('store_id', auth('customer')->user()->store?->id);
            });
    }
}
