<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Marketplace\Models\Store;
use Botble\Shortcode\Compilers\Shortcode;
use Botble\Shortcode\Facades\Shortcode as ShortcodeFacade;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Shortcode\ShortcodeField;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Arr;

app()->booted(function (): void {
    if (! is_plugin_active('marketplace')) {
        return;
    }

    add_shortcode('marketplace-stores', __('Marketplace Stores'), __('Marketplace Stores'), function (Shortcode $shortcode) {
        $storeIds = ShortcodeFacade::fields()->getIds('store_ids', $shortcode);

        if (empty($storeIds)) {
            return null;
        }

        $with = ['slugable'];
        if (EcommerceHelper::isReviewEnabled()) {
            $with['reviews'] = function ($query): void {
                $query->where([
                    'ec_products.status' => BaseStatusEnum::PUBLISHED,
                    'ec_reviews.status' => BaseStatusEnum::PUBLISHED,
                ]);
            };
        }

        $stores = Store::query()
            ->wherePublished()
            ->whereIn('id', $storeIds)
            ->with($with)
            ->withCount([
                'products' => function ($query): void {
                    $query->wherePublished();
                },
            ])
            ->orderByDesc('created_at')
            ->get();

        return Theme::partial('shortcodes.marketplace.stores.index', compact('shortcode', 'stores'));
    });

    shortcode()->setAdminConfig('marketplace-stores', function (array $attributes) {
        $stores = Store::query()
            ->wherePublished()
            ->oldest('name')
            ->pluck('name', 'id')
            ->all();

        return ShortcodeForm::createFromArray($attributes)
            ->withLazyLoading()
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle'))
            )
            ->add(
                'store_ids',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Stores'))
                    ->choices($stores)
                    ->multiple()
                    ->searchable()
                    ->selected(ShortcodeField::parseIds(Arr::get($attributes, 'store_ids')))
            );
    });
});
