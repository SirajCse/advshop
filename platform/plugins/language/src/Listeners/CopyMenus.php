<?php

namespace Botble\Language\Listeners;

use Botble\Base\Models\BaseModel;
use Botble\Language\Events\LanguageCreated;
use Botble\Language\Facades\Language as LanguageFacade;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Menu\Models\Menu;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;

class CopyMenus
{
    public function handle(LanguageCreated $event): void
    {
        if (! class_exists(Menu::class)) {
            return;
        }

        $defaultLocaleCode = LanguageFacade::getDefaultLocaleCode();

        if (! $defaultLocaleCode) {
            return;
        }

        $menus = Menu::query()
            ->with(['menuNodes', 'locations'])
            ->join('language_meta', 'language_meta.reference_id', '=', 'menus.id')
            ->where('language_meta.reference_type', Menu::class)
            ->where('language_meta.lang_meta_code', $defaultLocaleCode)
            ->select('menus.*')
            ->get();

        foreach ($menus as $menu) {
            if ($menu instanceof Menu) {
                $this->cloneMenu($menu, $event->language);
            }
        }
    }

    protected function cloneMenu(Menu $menu, Language $language): void
    {
        $menuItem = $menu->replicate();
        $menuItem->slug = $menu->slug . '-' . $language->lang_code;
        $menuItem->save();

        $originValue = LanguageMeta::query()
            ->where('reference_id', $menu->id)
            ->where('reference_type', Menu::class)
            ->value('lang_meta_origin');

        LanguageMeta::saveMetaData($menuItem, $language->lang_code, $originValue);

        $this->cloneMenuLocations($menu, $menuItem, $language);
        $this->cloneMenuNodes($menu, $menuItem, $language);
    }

    protected function cloneMenuLocations(Menu $sourceMenu, Menu $targetMenu, Language $language): void
    {
        foreach ($sourceMenu->locations as $location) {
            $menuLocationItem = $location->replicate();
            $menuLocationItem->menu_id = $targetMenu->getKey();
            $menuLocationItem->save();

            $originValue = LanguageMeta::query()
                ->where('reference_id', $location->id)
                ->where('reference_type', MenuLocation::class)
                ->value('lang_meta_origin');

            if ($menuLocationItem instanceof BaseModel) {
                LanguageMeta::saveMetaData($menuLocationItem, $language->lang_code, $originValue);
            }
        }
    }

    protected function cloneMenuNodes(Menu $sourceMenu, Menu $targetMenu, Language $language): void
    {
        $nodeIdMapping = [];

        $orderedNodes = $sourceMenu->menuNodes->sortBy('parent_id');

        foreach ($orderedNodes as $menuNode) {
            $menuNodeItem = $menuNode->replicate();
            $menuNodeItem->menu_id = $targetMenu->getKey();

            if ($menuNode->parent_id && isset($nodeIdMapping[$menuNode->parent_id])) {
                $menuNodeItem->parent_id = $nodeIdMapping[$menuNode->parent_id];
            }

            $menuNodeItem->save();

            $nodeIdMapping[$menuNode->id] = $menuNodeItem->id;

            $originValue = LanguageMeta::query()
                ->where('reference_id', $menuNode->id)
                ->where('reference_type', MenuNode::class)
                ->value('lang_meta_origin');

            if ($menuNodeItem instanceof BaseModel) {
                LanguageMeta::saveMetaData($menuNodeItem, $language->lang_code, $originValue);
            }
        }
    }
}
