<?php

namespace Botble\SalePopup\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\PanelSections\SettingEcommercePanelSection;
use Illuminate\Support\ServiceProvider;

class SalePopupServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->setNamespace('plugins/sale-popup')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadRoutes();

        PanelSectionManager::beforeRendering(function (): void {
            PanelSectionManager::default()
                ->registerItem(
                    SettingEcommercePanelSection::class,
                    fn () => PanelSectionItem::make('sale-popup')
                        ->setTitle(trans('plugins/ecommerce::setting.sale_popup.name'))
                        ->withIcon('ti ti-shopping-cart')
                        ->withDescription(trans('plugins/ecommerce::setting.sale_popup.description'))
                        ->withPriority(190)
                        ->withRoute('sale-popup.settings')
                );
        });

        $this->app->booted(function (): void {
            $this->app->register(HookServiceProvider::class);
        });
    }
}
