<?php

namespace Database\Seeders;

use Botble\Ads\Models\Ads;
use Botble\Base\Facades\MetaBox;
use Botble\Language\Models\LanguageMeta;
use Botble\LanguageAdvanced\Database\Seeders\BaseTranslationSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasMenuTranslationSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasPageTranslation;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasThemeOptionSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasWidgetSeeder;
use Botble\Menu\Facades\Menu;
use Botble\Menu\Models\Menu as MenuModel;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;
use Botble\Page\Models\Page;
use Botble\Setting\Facades\Setting;
use Botble\SimpleSlider\Models\SimpleSlider;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class TranslationSeeder extends BaseTranslationSeeder
{
    use HasMenuTranslationSeeder;
    use HasPageTranslation;
    use HasThemeOptionSeeder;
    use HasWidgetSeeder;

    public function run(): void
    {
        $locales = ['ar', 'vi', 'fr', 'id', 'tr'];

        $this->seedPageTranslations($locales);
        $this->seedThemeOptions($locales);
        $this->seedAllTranslatableModelsFromJson($locales);
        $this->seedProductContentTranslations($locales);
        $this->seedBlogPostContentTranslations($locales);
        $this->seedMenuTranslations($locales);
        $this->seedSimpleSliderTranslations($locales);
        $this->seedAdsMetaBoxTranslations($locales);
        $this->seedWidgets($locales);
        $this->seedSalePopupSettings($locales);
    }

    protected function getSkippedTables(): array
    {
        return ['pages'];
    }

    protected function getActiveThemeKey(): string
    {
        return match (setting('theme')) {
            'shofy-fashion' => 'fashion',
            'shofy-beauty' => 'beauty',
            'shofy-jewelry' => 'jewelry',
            'shofy-grocery' => 'grocery',
            default => 'main',
        };
    }

    protected function seedProductContentTranslations(array $locales): void
    {
        $this->updateTranslationContent('ec_products_translations', 'product-content.html', $locales);
    }

    protected function seedBlogPostContentTranslations(array $locales): void
    {
        $this->updateTranslationContent('posts_translations', 'blog-content.html', $locales);
    }

    protected function updateTranslationContent(string $translationTable, string $htmlFile, array $locales): void
    {
        if (! Schema::hasTable($translationTable)) {
            return;
        }

        foreach ($locales as $locale) {
            $path = database_path("seeders/translations/{$locale}/{$htmlFile}");

            if (! File::exists($path)) {
                continue;
            }

            $content = File::get($path);

            DB::table($translationTable)
                ->where('lang_code', $locale)
                ->update(['content' => $content]);
        }
    }

    protected function pageTranslations(): array
    {
        $themeKey = $this->getActiveThemeKey();

        $locales = [
            'ar' => [
                'home_name' => 'الرئيسية',
                'slider_key' => 'home-slider-ar',
                'free_delivery' => 'توصيل مجاني',
                'orders_from_all_item' => 'طلبات من جميع المنتجات',
                'return_refund' => 'إرجاع واسترداد',
                'money_back_guarantee' => 'ضمان استرداد الأموال',
                'member_discount' => 'خصم الأعضاء',
                'every_order_over' => 'كل طلب يزيد عن $140.00',
                'support_247' => 'دعم 24/7',
                'contact_us_24h' => 'اتصل بنا 24 ساعة في اليوم',
                'trending_products' => 'المنتجات الرائجة',
                'deal_of_the_day' => 'عرض اليوم',
                'view_all_deals' => 'عرض جميع العروض',
                'more_products' => 'المزيد من المنتجات',
                'new_arrivals' => 'وصل حديثاً',
                'latest_news' => 'آخر الأخبار والمقالات',
                'view_all' => 'عرض الكل',
                'coming_soon_title' => 'كن أول من يعلم عند الإطلاق',
                'coming_soon_address' => ' 58 شارع كوميرشال رود فراتون، أستراليا',
                'coming_soon_business_hours' => 'الاثنين – السبت: 8 ص – 5 م، الأحد: مغلق',
            ],
            'vi' => [
                'home_name' => 'Trang chủ',
                'slider_key' => 'home-slider-vi',
                'free_delivery' => 'Giao hàng miễn phí',
                'orders_from_all_item' => 'Đơn hàng từ tất cả sản phẩm',
                'return_refund' => 'Đổi trả & Hoàn tiền',
                'money_back_guarantee' => 'Đảm bảo hoàn tiền',
                'member_discount' => 'Giảm giá thành viên',
                'every_order_over' => 'Mọi đơn hàng trên $140.00',
                'support_247' => 'Hỗ trợ 24/7',
                'contact_us_24h' => 'Liên hệ chúng tôi 24 giờ mỗi ngày',
                'trending_products' => 'Sản phẩm thịnh hành',
                'deal_of_the_day' => 'Ưu đãi trong ngày',
                'view_all_deals' => 'Xem tất cả ưu đãi',
                'more_products' => 'Thêm sản phẩm',
                'new_arrivals' => 'Hàng mới về',
                'latest_news' => 'Tin tức & bài viết mới nhất',
                'view_all' => 'Xem tất cả',
                'coming_soon_title' => 'Nhận thông báo khi chúng tôi ra mắt',
                'coming_soon_address' => ' 58 Đường Commercial Road Fratton, Úc',
                'coming_soon_business_hours' => 'Thứ 2 – Thứ 7: 8 giờ sáng – 5 giờ chiều, Chủ nhật: ĐÓNG CỬA',
            ],
            'fr' => [
                'home_name' => 'Accueil',
                'slider_key' => 'home-slider-fr',
                'free_delivery' => 'Livraison gratuite',
                'orders_from_all_item' => 'Commandes de tous les articles',
                'return_refund' => 'Retour & Remboursement',
                'money_back_guarantee' => 'Garantie satisfait ou remboursé',
                'member_discount' => 'Réduction membre',
                'every_order_over' => 'Chaque commande de plus de $140.00',
                'support_247' => 'Support 24/7',
                'contact_us_24h' => 'Contactez-nous 24h/24',
                'trending_products' => 'Produits tendance',
                'deal_of_the_day' => 'Offre du jour',
                'view_all_deals' => 'Voir toutes les offres',
                'more_products' => 'Plus de produits',
                'new_arrivals' => 'Nouveautés',
                'latest_news' => 'Dernières nouvelles et articles',
                'view_all' => 'Voir tout',
                'coming_soon_title' => 'Soyez notifié lors de notre lancement',
                'coming_soon_address' => ' 58 Street Commercial Road Fratton, Australie',
                'coming_soon_business_hours' => 'Lun – Sam : 8h – 17h, Dimanche : FERMÉ',
            ],
            'id' => [
                'home_name' => 'Beranda',
                'slider_key' => 'home-slider-id',
                'free_delivery' => 'Gratis Ongkir',
                'orders_from_all_item' => 'Pesanan dari semua produk',
                'return_refund' => 'Pengembalian & Refund',
                'money_back_guarantee' => 'Jaminan uang kembali',
                'member_discount' => 'Diskon Member',
                'every_order_over' => 'Setiap pesanan di atas $140.00',
                'support_247' => 'Dukungan 24/7',
                'contact_us_24h' => 'Hubungi kami 24 jam sehari',
                'trending_products' => 'Produk Terlaris',
                'deal_of_the_day' => 'Penawaran Hari Ini',
                'view_all_deals' => 'Lihat Semua Penawaran',
                'more_products' => 'Produk Lainnya',
                'new_arrivals' => 'Produk Terbaru',
                'latest_news' => 'Berita & artikel terbaru',
                'view_all' => 'Lihat Semua',
                'coming_soon_title' => 'Dapatkan Pemberitahuan Saat Kami Meluncurkan',
                'coming_soon_address' => ' 58 Street Commercial Road Fratton, Australia',
                'coming_soon_business_hours' => 'Sen – Sab: 8 pagi – 5 sore, Minggu: TUTUP',
            ],
            'tr' => [
                'home_name' => 'Ana Sayfa',
                'slider_key' => 'home-slider-tr',
                'free_delivery' => 'Ücretsiz Teslimat',
                'orders_from_all_item' => 'Tüm ürünlerden siparişler',
                'return_refund' => 'İade & Geri Ödeme',
                'money_back_guarantee' => 'Para iade garantisi',
                'member_discount' => 'Üye İndirimi',
                'every_order_over' => '$140.00 üzerindeki her sipariş',
                'support_247' => '7/24 Destek',
                'contact_us_24h' => 'Günde 24 saat bize ulaşın',
                'trending_products' => 'Trend Ürünler',
                'deal_of_the_day' => 'Günün Fırsatı',
                'view_all_deals' => 'Tüm Fırsatları Gör',
                'more_products' => 'Daha Fazla Ürün',
                'new_arrivals' => 'Yeni Gelenler',
                'latest_news' => 'Son haberler ve makaleler',
                'view_all' => 'Tümünü Gör',
                'coming_soon_title' => 'Lansman Yapıldığında Bilgilendirilin',
                'coming_soon_address' => ' 58 Street Commercial Road Fratton, Avustralya',
                'coming_soon_business_hours' => 'Pzt – Cmt: 08:00 – 17:00, Pazar: KAPALI',
            ],
        ];

        $comingSoonCountdownTime = '';
        $comingSoonPage = Page::query()->where('name', 'Coming Soon')->first();

        if ($comingSoonPage) {
            if (preg_match('/countdown_time="([^"]*)"/', $comingSoonPage->getRawOriginal('content'), $matches)) {
                $comingSoonCountdownTime = $matches[1];
            }
        }

        $translations = [];

        foreach ($locales as $locale => $t) {
            $translations[$locale] = [
                'Home' => [
                    'name' => $t['home_name'],
                    'content' => $this->buildHomePageContent($themeKey, $locale, $t),
                ],
                'Coming Soon' => [
                    'content' => '[coming-soon title="' . $t['coming_soon_title'] . '" countdown_time="' . $comingSoonCountdownTime . '" address="' . $t['coming_soon_address'] . '" hotline="+123456789" business_hours="' . $t['coming_soon_business_hours'] . '" show_social_links="0,1" image="main/general/contact-img.jpg"][/coming-soon]',
                ],
                'Our Story' => [
                    'content' => $this->loadTranslatedHtml('our-story.html', $locale),
                ],
                'Careers' => [
                    'content' => $this->loadTranslatedHtml('careers.html', $locale),
                ],
                'Cookie Policy' => [
                    'content' => $this->loadTranslatedHtml('cookie-policy.html', $locale),
                ],
                'Shipping' => [
                    'content' => $this->loadTranslatedHtml('shipping.html', $locale),
                ],
                'Return Policy' => [
                    'content' => $this->loadTranslatedHtml('return-policy.html', $locale),
                ],
            ];
        }

        return $translations;
    }

    protected function seedMenuTranslations(array $locales): void
    {
        $mainMenu = MenuModel::query()->where('slug', 'main-menu')->first();
        $myAccountMenu = MenuModel::query()->where('slug', 'my-account')->first();
        $informationMenu = MenuModel::query()->where('slug', 'information')->first();

        if (! $mainMenu && ! $myAccountMenu && ! $informationMenu) {
            return;
        }

        $mainMenuOrigin = $mainMenu ? $this->getLanguageMetaOrigin($mainMenu) : null;
        $myAccountMenuOrigin = $myAccountMenu ? $this->getLanguageMetaOrigin($myAccountMenu) : null;
        $informationMenuOrigin = $informationMenu ? $this->getLanguageMetaOrigin($informationMenu) : null;

        $mainMenuLocation = $mainMenu
            ? MenuLocation::query()
                ->where('menu_id', $mainMenu->getKey())
                ->where('location', 'main-menu')
                ->first()
            : null;

        $locationOrigin = $mainMenuLocation ? $this->getLanguageMetaOrigin($mainMenuLocation) : null;

        $pageIds = Page::query()->pluck('id', 'name')->all();

        foreach ($locales as $locale) {
            $translations = $this->loadMenuTranslations($locale);

            if (empty($translations)) {
                continue;
            }

            if ($mainMenu) {
                $this->createMenuTranslation(
                    $locale,
                    'main-menu',
                    $translations['name'],
                    $this->buildMainMenuItems($translations, $pageIds),
                    $mainMenuOrigin,
                    $locationOrigin
                );
            }

            if ($myAccountMenu) {
                $this->createMenuTranslation(
                    $locale,
                    'my-account',
                    $translations['my_account_name'] ?? 'My Account',
                    $this->buildMyAccountMenuItems($translations, $pageIds),
                    $myAccountMenuOrigin,
                    null
                );
            }

            if ($informationMenu) {
                $this->createMenuTranslation(
                    $locale,
                    'information',
                    $translations['information_name'] ?? 'Information',
                    $this->buildInformationMenuItems($translations, $pageIds),
                    $informationMenuOrigin,
                    null
                );
            }
        }

        Menu::clearCacheMenuItems();
    }

    protected function buildMainMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['home'] ?? 'Home',
                'reference_id' => $pageIds['Home'] ?? 1,
                'reference_type' => Page::class,
                'children' => [
                    [
                        'title' => $labels['electronics'] ?? 'Electronics',
                        'url' => 'https://shofy.botble.com',
                    ],
                    [
                        'title' => $labels['fashion'] ?? 'Fashion',
                        'url' => 'https://shofy-fashion.botble.com',
                    ],
                    [
                        'title' => $labels['beauty'] ?? 'Beauty',
                        'url' => 'https://shofy-beauty.botble.com',
                    ],
                    [
                        'title' => $labels['jewelry'] ?? 'Jewelry',
                        'url' => 'https://shofy-jewelry.botble.com',
                    ],
                    [
                        'title' => $labels['grocery'] ?? 'Grocery',
                        'url' => 'https://shofy-grocery.botble.com',
                    ],
                ],
            ],
            [
                'title' => $labels['shop'] ?? 'Shop',
                'url' => '#',
                'children' => [
                    [
                        'title' => $labels['shop_categories'] ?? 'Shop Categories',
                        'reference_id' => $pageIds['Categories'] ?? null,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['shop_brands'] ?? 'Shop Brands',
                        'reference_id' => $pageIds['Brands'] ?? null,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['shop_list'] ?? 'Shop List',
                        'url' => '/products?layout=list',
                    ],
                    [
                        'title' => $labels['shop_grid'] ?? 'Shop Grid',
                        'url' => '/products?layout=grid',
                    ],
                    [
                        'title' => $labels['product_detail'] ?? 'Product Detail',
                        'url' => '/products',
                    ],
                    [
                        'title' => $labels['grab_coupons'] ?? 'Grab Coupons',
                        'reference_id' => $pageIds['Coupons'] ?? null,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['cart'] ?? 'Cart',
                        'url' => '/cart',
                    ],
                    [
                        'title' => $labels['compare'] ?? 'Compare',
                        'url' => '/compare',
                    ],
                    [
                        'title' => $labels['wishlist'] ?? 'Wishlist',
                        'url' => '/wishlist',
                    ],
                    [
                        'title' => $labels['track_your_order'] ?? 'Track Your Order',
                        'url' => '/orders/tracking',
                    ],
                ],
            ],
            [
                'title' => $labels['vendors'] ?? 'Vendors',
                'url' => '/stores',
            ],
            [
                'title' => $labels['pages'] ?? 'Pages',
                'url' => '#',
                'children' => [
                    [
                        'title' => $labels['faqs'] ?? 'FAQs',
                        'reference_id' => $pageIds['FAQs'] ?? null,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['login'] ?? 'Login',
                        'url' => '/login',
                    ],
                    [
                        'title' => $labels['register'] ?? 'Register',
                        'url' => '/register',
                    ],
                    [
                        'title' => $labels['forgot_password'] ?? 'Forgot Password',
                        'url' => '/password/reset',
                    ],
                    [
                        'title' => $labels['error_404'] ?? '404 Error',
                        'url' => '/404',
                    ],
                    [
                        'title' => $labels['coming_soon'] ?? 'Coming Soon',
                        'url' => '/coming-soon',
                    ],
                ],
            ],
            [
                'title' => $labels['blog'] ?? 'Blog',
                'reference_id' => $pageIds['Blog'] ?? null,
                'reference_type' => Page::class,
                'children' => [
                    [
                        'title' => $labels['blog_grid'] ?? 'Blog Grid',
                        'url' => '/blog?layout=grid',
                    ],
                    [
                        'title' => $labels['blog_list'] ?? 'Blog List',
                        'url' => '/blog?layout=list',
                    ],
                    [
                        'title' => $labels['blog_detail'] ?? 'Blog Detail',
                        'url' => '/blog',
                    ],
                ],
            ],
            [
                'title' => $labels['contact'] ?? 'Contact',
                'reference_id' => $pageIds['Contact'] ?? null,
                'reference_type' => Page::class,
            ],
        ];
    }

    protected function buildMyAccountMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['track_orders'] ?? 'Track Orders',
                'url' => '/orders/tracking',
            ],
            [
                'title' => $labels['shipping'] ?? 'Shipping',
                'reference_id' => $pageIds['Shipping'] ?? null,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['wishlist'] ?? 'Wishlist',
                'url' => '/wishlist',
            ],
            [
                'title' => $labels['my_account'] ?? 'My Account',
                'url' => '/customer/overview',
            ],
            [
                'title' => $labels['order_history'] ?? 'Order History',
                'url' => '/customer/orders',
            ],
            [
                'title' => $labels['returns'] ?? 'Returns',
                'url' => '/customer/order-returns',
            ],
        ];
    }

    protected function buildInformationMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['our_story'] ?? 'Our Story',
                'reference_id' => $pageIds['Our Story'] ?? null,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['careers'] ?? 'Careers',
                'reference_id' => $pageIds['Careers'] ?? null,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['privacy_policy'] ?? 'Privacy Policy',
                'reference_id' => $pageIds['Cookie Policy'] ?? null,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['latest_news'] ?? 'Latest News',
                'url' => '/blog',
            ],
            [
                'title' => $labels['contact_us'] ?? 'Contact Us',
                'reference_id' => $pageIds['Contact'] ?? null,
                'reference_type' => Page::class,
            ],
        ];
    }

    protected function seedSimpleSliderTranslations(array $locales): void
    {
        $slider = SimpleSlider::query()->where('key', 'home-slider')->first();

        if (! $slider) {
            return;
        }

        $sliderOrigin = $this->getLanguageMetaOrigin($slider);
        $sliderItems = SimpleSliderItem::query()
            ->where('simple_slider_id', $slider->id)
            ->orderBy('order')
            ->get();

        $themeKey = $this->getActiveThemeKey();
        $sliderTranslations = $this->getSliderTranslations($themeKey);

        foreach ($locales as $locale) {
            $data = $sliderTranslations[$locale] ?? null;

            if (! $data) {
                continue;
            }

            $translatedSlider = SimpleSlider::query()->create([
                'name' => $data['name'],
                'key' => 'home-slider-' . $locale,
            ]);

            LanguageMeta::saveMetaData($translatedSlider, $locale, $sliderOrigin);

            foreach ($data['items'] as $index => $itemData) {
                $originalItem = $sliderItems[$index] ?? null;

                if (! $originalItem) {
                    continue;
                }

                $sliderItem = SimpleSliderItem::query()->create([
                    'title' => $itemData['title'],
                    'description' => $itemData['description'] ?? $originalItem->description,
                    'link' => $originalItem->link,
                    'image' => $originalItem->image,
                    'order' => $originalItem->order,
                    'simple_slider_id' => $translatedSlider->id,
                ]);

                if (isset($itemData['subtitle'])) {
                    MetaBox::saveMetaBoxData($sliderItem, 'subtitle', $itemData['subtitle']);
                }

                if (isset($itemData['button_label'])) {
                    MetaBox::saveMetaBoxData($sliderItem, 'button_label', $itemData['button_label']);
                }

                MetaBox::saveMetaBoxData($sliderItem, 'background_color', MetaBox::getMetaData($originalItem, 'background_color', true));
                MetaBox::saveMetaBoxData($sliderItem, 'is_light', MetaBox::getMetaData($originalItem, 'is_light', true));
            }
        }
    }

    protected function seedAdsMetaBoxTranslations(array $locales): void
    {
        $ads = Ads::query()->get();

        if ($ads->isEmpty()) {
            return;
        }

        foreach ($locales as $locale) {
            $dictionary = $this->loadTranslations('ads', $locale);

            if (empty($dictionary)) {
                continue;
            }

            foreach ($ads as $ad) {
                foreach (['title', 'subtitle', 'button_label'] as $metaKey) {
                    $originalValue = MetaBox::getMetaData($ad, $metaKey, true);

                    if (! $originalValue) {
                        continue;
                    }

                    $translatedValue = $this->translateValue($dictionary, $originalValue);

                    MetaBox::saveMetaBoxData($ad, $locale . '_' . $metaKey, $translatedValue);
                }
            }
        }
    }

    protected function createMenuNode(int $position, array $menuNode, int|string $menuId, int|string $parentId = 0): void
    {
        $menuNode['menu_id'] = $menuId;
        $menuNode['parent_id'] = $parentId;
        $menuNode['position'] = $position;

        if (isset($menuNode['url'])) {
            $menuNode['url'] = str_replace(url(''), '', $menuNode['url']);
        }

        if (Arr::has($menuNode, 'children') && ! empty($menuNode['children'])) {
            $children = $menuNode['children'];
            $menuNode['has_child'] = true;
        } else {
            $children = [];
            $menuNode['has_child'] = false;
        }

        Arr::forget($menuNode, 'children');

        $createdNode = MenuNode::query()->create($menuNode);

        foreach ($children as $childPosition => $child) {
            $this->createMenuNode($childPosition, $child, $menuId, $createdNode->getKey());
        }
    }

    protected function seedSalePopupSettings(array $locales): void
    {
        $settings = [
            'ar' => [
                'list_sale_time' => 'منذ 4 ساعات | منذ ساعتين | منذ 45 دقيقة | منذ يوم واحد | منذ 8 ساعات | منذ 10 ساعات | منذ 25 دقيقة | منذ يومين | منذ 5 ساعات | منذ 40 دقيقة',
            ],
            'vi' => [
                'list_sale_time' => '4 giờ trước | 2 giờ trước | 45 phút trước | 1 ngày trước | 8 giờ trước | 10 giờ trước | 25 phút trước | 2 ngày trước | 5 giờ trước | 40 phút trước',
            ],
            'fr' => [
                'list_sale_time' => 'il y a 4 heures | il y a 2 heures | il y a 45 minutes | il y a 1 jour | il y a 8 heures | il y a 10 heures | il y a 25 minutes | il y a 2 jours | il y a 5 heures | il y a 40 minutes',
            ],
            'id' => [
                'list_sale_time' => '4 jam yang lalu | 2 jam yang lalu | 45 menit yang lalu | 1 hari yang lalu | 8 jam yang lalu | 10 jam yang lalu | 25 menit yang lalu | 2 hari yang lalu | 5 jam yang lalu | 40 menit yang lalu',
            ],
            'tr' => [
                'list_sale_time' => '4 saat önce | 2 saat önce | 45 dakika önce | 1 gün önce | 8 saat önce | 10 saat önce | 25 dakika önce | 2 gün önce | 5 saat önce | 40 dakika önce',
            ],
        ];

        foreach ($locales as $locale) {
            $localeSettings = $settings[$locale] ?? null;

            if (! $localeSettings) {
                continue;
            }

            foreach ($localeSettings as $key => $value) {
                Setting::set("sale_popup_{$key}-{$locale}", $value);
            }
        }

        Setting::save();
    }

    protected function applyWidgetTranslations(array $data, array $translations, string $locale): array
    {
        foreach (['name', 'title', 'subtitle', 'about', 'content', 'description', 'phone_label'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = $this->translateValue($translations, $data[$key]);
            }
        }

        $localizeMenuSlugs = ['my-account', 'information'];
        if (isset($data['menu_id']) && in_array($data['menu_id'], $localizeMenuSlugs, true)) {
            $data['menu_id'] = $this->localizedSlug($data['menu_id'], $locale);
        }

        if (isset($data['messages']) && is_array($data['messages'])) {
            foreach ($data['messages'] as $msgIndex => $message) {
                foreach ($message as $fieldIndex => $field) {
                    $key = Arr::get($field, 'key');
                    $value = Arr::get($field, 'value');

                    if ($key === 'message' && is_string($value)) {
                        $data['messages'][$msgIndex][$fieldIndex]['value'] = $this->translateValue(
                            $translations,
                            $value
                        );
                    }
                }
            }
        }

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemIndex => $item) {
                foreach ($item as $fieldIndex => $field) {
                    $key = Arr::get($field, 'key');
                    $value = Arr::get($field, 'value');

                    if (! is_string($value)) {
                        continue;
                    }

                    if (in_array($key, ['label', 'text'], true)) {
                        $data['items'][$itemIndex][$fieldIndex]['value'] = $this->translateValue(
                            $translations,
                            $value
                        );
                    }
                }
            }
        }

        return $data;
    }

    protected function loadTranslatedHtml(string $filename, string $locale): string
    {
        $path = database_path("seeders/translations/{$locale}/{$filename}");

        return File::exists($path) ? File::get($path) : '';
    }

    protected function buildHomePageContent(string $themeKey, string $locale, array $t): string
    {
        return match ($themeKey) {
            'fashion' => $this->buildFashionHomeContent($locale, $t),
            'beauty' => $this->buildBeautyHomeContent($locale, $t),
            'jewelry' => $this->buildJewelryHomeContent($locale, $t),
            'grocery' => $this->buildGroceryHomeContent($locale, $t),
            default => $this->buildMainHomeContent($locale, $t),
        };
    }

    protected function buildMainHomeContent(string $locale, array $t): string
    {
        return '[simple-slider style="1" key="' . $t['slider_key'] . '" customize_font_family_of_description="1" font_family_of_description="Oregano" shape_1="main/sliders/shape-1.png" shape_2="main/sliders/shape-2.png" shape_3="main/sliders/shape-3.png" shape_4="main/sliders/shape-4.png" is_autoplay="yes" autoplay_speed="5000"][/simple-slider]' .
            '[ecommerce-categories style="slider" category_ids="6,10,13,16,30" enable_lazy_loading="no"][/ecommerce-categories]' .
            '[site-features style="1" quantity="4" title_1="' . $t['free_delivery'] . '" description_1="' . $t['orders_from_all_item'] . '" icon_1="ti ti-truck-delivery" title_2="' . $t['return_refund'] . '" description_2="' . $t['money_back_guarantee'] . '" icon_2="ti ti-currency-dollar" title_3="' . $t['member_discount'] . '" description_3="' . $t['every_order_over'] . '" icon_3="ti ti-discount-2" title_4="' . $t['support_247'] . '" description_4="' . $t['contact_us_24h'] . '" icon_4="ti ti-headset" enable_lazy_loading="no"][/site-features]' .
            '[ecommerce-product-groups title="' . $t['trending_products'] . '" limit="8" tabs="all,featured,on-sale,trending,top-rated" enable_lazy_loading="yes"][/ecommerce-product-groups]' .
            '[ads style="1" key_1="UROL9F9ZZVAA" key_2="B30VDBKO7SBF" enable_lazy_loading="yes"][/ads]' .
            '[ecommerce-flash-sale style="1" title="' . $t['deal_of_the_day'] . '" flash_sale_id="1" limit="4" button_label="' . $t['view_all_deals'] . '" button_url="/products" enable_lazy_loading="yes"][/ecommerce-flash-sale]' .
            '[ecommerce-products style="grid" category_ids="20" limit="12" with_sidebar="1" image="main/gadgets/gadget-girl.png" action_label="' . $t['more_products'] . '" ads_ids="3,4" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[ads style="4" key_1="B5ZA76ZWMWAE" key_2="F1LTQS976YPY" key_3="IHPZ2WBSYJUK" enable_lazy_loading="yes"][/ads]' .
            '[ecommerce-products style="slider" title="' . $t['new_arrivals'] . '" by="collection" collection_ids="1" limit="12" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[ecommerce-product-groups style="columns" limit="3" tabs="on-sale,trending,top-rated" enable_lazy_loading="yes"][/ecommerce-product-groups]' .
            '[blog-posts title="' . $t['latest_news'] . '" type="latest" limit="3" button_label="' . $t['view_all'] . '" button_url="/blog" enable_lazy_loading="yes"][/blog-posts]' .
            '[gallery style="1" limit="5" enable_lazy_loading="yes"][/gallery]';
    }

    protected function buildFashionHomeContent(string $locale, array $t): string
    {
        $themeT = $this->getFashionPageTranslations($locale);

        return '[simple-slider style="2" key="' . $t['slider_key'] . '" shape_1="fashion/sliders/shape-1.png" shape_2="fashion/sliders/shape-2.png" shape_3="fashion/sliders/shape-3.png" is_autoplay="yes" autoplay_speed="5000"][/simple-slider]' .
            '[ads style="2" key_1="WXAUTIJV1QU0" key_2="7Z5RXBBWV7J2" key_3="JY08TDO8FG1E" full_width="1" enable_lazy_loading="no"][/ads]' .
            '[ecommerce-categories style="slider" title="' . $themeT['popular_on_shofy'] . '" subtitle="' . $themeT['shop_by_category'] . '" category_ids="1,2,7,11,18,19" enable_lazy_loading="no"][/ecommerce-categories]' .
            '[ecommerce-product-groups title="' . $themeT['customer_favorite'] . '" subtitle="' . $themeT['all_product_shop'] . '" limit="8" tabs="all,featured,on-sale,trending,top-rated" enable_lazy_loading="yes"][/ecommerce-product-groups]' .
            '[ecommerce-products style="slider-full-width" title="' . $themeT['this_weeks_featured'] . '" subtitle="' . $themeT['shop_by_category'] . '" collection_ids="1" limit="5" with_sidebar="1" background_color="#EFF1F5" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[ecommerce-products title="' . $themeT['trending_arrivals'] . '" subtitle="' . $themeT['more_to_discover'] . '" collection_ids="1" limit="5" with_sidebar="1" ads_ids="6" style="slider" ads="VKJNCBIBQC1O" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[ecommerce-products title="' . $themeT['this_weeks_featured'] . '" subtitle="' . $themeT['best_seller_weeks'] . '" by="specify" product_ids="3,4,5,6" limit="12" style="grid" button_label="' . $themeT['shop_all_now'] . '" button_url="/products" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[testimonials style="1" title="' . $themeT['the_review_are_in'] . '" testimonial_ids="2,3,4" enable_lazy_loading="yes"][/testimonials]' .
            '[blog-posts title="' . $t['latest_news'] . '" subtitle="' . $themeT['our_blog_news'] . '" type="recent" limit="3" button_label="' . $themeT['discover_more'] . '" button_url="/blog" enable_lazy_loading="yes"][/blog-posts]' .
            '[site-features style="2" quantity="4" title_1="' . $t['free_delivery'] . '" description_1="' . $t['orders_from_all_item'] . '" icon_1="ti ti-truck-delivery" title_2="' . $t['return_refund'] . '" description_2="' . $t['money_back_guarantee'] . '" icon_2="ti ti-currency-dollar" title_3="' . $t['member_discount'] . '" description_3="' . $t['every_order_over'] . '" icon_3="ti ti-discount-2" title_4="' . $t['support_247'] . '" description_4="' . $t['contact_us_24h'] . '" icon_4="ti ti-headset" enable_lazy_loading="yes"][/site-features]' .
            '[gallery style="2" limit="5" enable_lazy_loading="yes"][/gallery]';
    }

    protected function buildBeautyHomeContent(string $locale, array $t): string
    {
        $themeT = $this->getBeautyPageTranslations($locale);

        return '[simple-slider customize_font_family_of_description="1" font_family_of_description="Charm" style="3" key="' . $t['slider_key'] . '"][/simple-slider]' .
            '[ecommerce-categories style="grid" category_ids="1,2,7,17" background_color="#F3F5F7" title="' . $themeT['discover_our_products'] . '" subtitle="' . $themeT['product_collection'] . '" button_label="' . $themeT['shop_all_products'] . '" button_url="/products" enable_lazy_loading="no"][/ecommerce-categories]' .
            '[ecommerce-products style="simple" by="specify" product_ids="2,39,41" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[ecommerce-products style="grid" title="' . $themeT['best_sellers_beauty'] . '" subtitle="' . $themeT['shop_by_category'] . '" by="category" category_ids="2,3,4" limit="8" background_color="rgb(234, 228, 222)" button_label="' . $themeT['shop_all_products'] . '" button_url="/products" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[ecommerce-product-groups title="' . $themeT['enjoy_best_quality'] . '" subtitle="' . $themeT['best_seller_weeks'] . '" limit="8" tabs="all,featured,on-sale,trending,top-rated" enable_lazy_loading="yes"][/ecommerce-product-groups]' .
            '[testimonials style="2" title="' . $themeT['what_clients_say'] . '" subtitle="' . $themeT['customers_review'] . '" testimonial_ids="1,2,3,4" enable_lazy_loading="yes"][/testimonials]' .
            '[site-features style="2" quantity="4" title_1="' . $t['free_delivery'] . '" description_1="' . $t['orders_from_all_item'] . '" icon_1="ti ti-truck-delivery" title_2="' . $t['return_refund'] . '" description_2="' . $t['money_back_guarantee'] . '" icon_2="ti ti-currency-dollar" title_3="' . $t['member_discount'] . '" description_3="' . $t['every_order_over'] . '" icon_3="ti ti-discount-2" title_4="' . $t['support_247'] . '" description_4="' . $t['contact_us_24h'] . '" icon_4="ti ti-headset" enable_lazy_loading="yes"][/site-features]' .
            '[gallery style="2" limit="6" enable_lazy_loading="yes"][/gallery]';
    }

    protected function buildJewelryHomeContent(string $locale, array $t): string
    {
        $themeT = $this->getJewelryPageTranslations($locale);

        return '[simple-slider style="4" key="' . $t['slider_key'] . '" customize_font_family_of_description="1" font_family_of_description="Charm" shape_1="fashion/sliders/shape-1.png" shape_2="fashion/sliders/shape-2.png" shape_3="fashion/sliders/shape-3.png" is_autoplay="yes" autoplay_speed="5000"][/simple-slider]' .
            '[site-features style="3" quantity="4" title_1="' . $t['free_delivery'] . '" description_1="' . $t['orders_from_all_item'] . '" icon_1="ti ti-truck-delivery" title_2="' . $t['return_refund'] . '" description_2="' . $t['money_back_guarantee'] . '" icon_2="ti ti-currency-dollar" title_3="' . $t['member_discount'] . '" description_3="' . $t['every_order_over'] . '" icon_3="ti ti-discount-2" title_4="' . $t['support_247'] . '" description_4="' . $t['contact_us_24h'] . '" icon_4="ti ti-headset" enable_lazy_loading="no"][/site-features]' .
            '[ads style="3" key_1="UROL9F9ZZVAA" key_2="B30VDBKO7SBF" key_3="BN3ZCHLIE95I" key_4="QGPRRJ2MPZYA" enable_lazy_loading="no"][/ads]' .
            '[about image_1="main/general/about-1.jpg" image_2="main/general/about-2.jpg" subtitle="' . $themeT['unity_collection'] . '" title="' . $themeT['shop_limited_edition'] . '" description="' . $themeT['about_description'] . '" action_label="' . $themeT['contact_us_label'] . '" action_url="/contact" enable_lazy_loading="yes"][/about]' .
            '[ecommerce-products style="slider-full-width" title="' . $themeT['this_weeks_featured'] . '" subtitle="' . $themeT['shop_by_category'] . '" collection_ids="1" limit="5" with_sidebar="1" background_color="#EFF1F5" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[ecommerce-product-groups title="' . $themeT['discover_products'] . '" subtitle="' . $themeT['product_collection'] . '" limit="8" tabs="all,featured,on-sale,trending,top-rated" enable_lazy_loading="yes"][/ecommerce-product-groups]' .
            '[ecommerce-products style="slider" title="' . $themeT['top_sellers_dress'] . '" subtitle="' . $themeT['best_seller_weeks'] . '" by="collection" collection_ids="2" limit="5" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[image-slider type="custom" quantity="4" name_1="Brandit" image_1="main/brands/1.png" url_1="https://brandit-wear.com" name_2="Vintage" image_2="main/brands/2.png" url_2="https://vintagebrand.com/" name_3="Showtime" image_3="main/brands/3.png" url_3="https://www.showtime.com/" name_4="Classic Design Studio" image_4="main/brands/5.png" url_4="http://www.classicdesignstudios.com/" enable_lazy_loading="yes"][/image-slider]' .
            '[gallery style="2" title="' . $themeT['trends_image_feed'] . '" subtitle="' . $themeT['gallery_subtitle'] . '" limit="6" enable_lazy_loading="yes"][/gallery]';
    }

    protected function buildGroceryHomeContent(string $locale, array $t): string
    {
        $themeT = $this->getGroceryPageTranslations($locale);

        return '[simple-slider customize_font_family_of_description="1" font_family_of_description="Charm" style="5" key="' . $t['slider_key'] . '" shape_1="grocery/sliders/shape-1.png" shape_2="grocery/sliders/shape-2.png" shape_3="grocery/sliders/shape-3.png" shape_4="grocery/sliders/shape-4.png" is_autoplay="yes" autoplay_speed="5000"][/simple-slider]' .
            '[ecommerce-categories category_ids="1,5,9,13,14,18" title="' . $themeT['popular_on_shofy'] . '" subtitle="' . $themeT['shop_by_category'] . '" enable_lazy_loading="no"][/ecommerce-categories]' .
            '[ecommerce-product-groups style="tabs" title="' . $t['trending_products'] . '" subtitle="' . $themeT['all_product_shop'] . '" limit="8" tabs="all,featured,on-sale,trending,top-rated" enable_lazy_loading="yes"][/ecommerce-product-groups]' .
            '[ecommerce-flash-sale style="2" title="' . $themeT['grab_best_offer'] . '" subtitle="' . $themeT['best_deals_week'] . '" flash_sale_id="1" limit="3" background_image="grocery/banners/3.png" enable_lazy_loading="yes"][/ecommerce-flash-sale]' .
            '[ecommerce-product-groups style="columns" tabs="trending,top-rated" limit="3" ads="GA3K1VZWNMPF" enable_lazy_loading="yes"][/ecommerce-product-groups]' .
            '[testimonials style="3" title="' . $themeT['happy_customers'] . '" subtitle="' . $themeT['customer_reviews'] . '" testimonial_ids="1,2,3,4" enable_lazy_loading="yes"][/testimonials]' .
            '[ecommerce-products style="slider" title="' . $themeT['bestsellers_week'] . '" subtitle="' . $themeT['more_to_discover'] . '" category_ids="5" limit="5" with_sidebar="1" ads_ids="3,4" enable_lazy_loading="yes"][/ecommerce-products]' .
            '[site-features style="4" quantity="4" title_1="' . $themeT['flexible_delivery'] . '" icon_1="ti ti-truck-delivery" title_2="' . $themeT['money_back_100'] . '" icon_2="ti ti-currency-dollar" title_3="' . $themeT['secure_payment'] . '" icon_3="ti ti-credit-card" title_4="' . $themeT['hour_support_24'] . '" icon_4="ti ti-headset" enable_lazy_loading="yes"][/site-features]' .
            '[app-downloads title="' . $themeT['get_app_groceries'] . '" google_label="Google Play" google_icon="ti ti-brand-google-play" google_url="https://play.google.com/" apple_label="Apple Store" apple_icon="ti ti-brand-apple-filled" apple_url="https://apps.apple.com/" screenshot="main/general/cta-thumb-1.jpg" shape_image_left="main/general/cta-shape-1.png" shape_image_right="main/general/cta-shape-2.png" enable_lazy_loading="yes"][/app-downloads]';
    }

    protected function getFashionPageTranslations(string $locale): array
    {
        $translations = [
            'ar' => [
                'popular_on_shofy' => 'شائع في متجر Shofy.',
                'shop_by_category' => 'تسوق حسب الفئة',
                'customer_favorite' => 'منتجات الأنماط المفضلة للعملاء',
                'all_product_shop' => 'جميع المنتجات',
                'this_weeks_featured' => 'مميز هذا الأسبوع',
                'trending_arrivals' => 'وصول رائج',
                'more_to_discover' => 'المزيد للاكتشاف',
                'shop_all_now' => 'تسوق الكل الآن',
                'best_seller_weeks' => 'الأكثر مبيعاً هذا الأسبوع',
                'the_review_are_in' => 'التقييمات وصلت',
                'our_blog_news' => 'مدونتنا وأخبارنا',
                'discover_more' => 'اكتشف المزيد',
            ],
            'vi' => [
                'popular_on_shofy' => 'Phổ biến trên cửa hàng Shofy.',
                'shop_by_category' => 'Mua sắm theo danh mục',
                'customer_favorite' => 'Sản phẩm phong cách yêu thích',
                'all_product_shop' => 'Tất cả sản phẩm',
                'this_weeks_featured' => 'Nổi bật tuần này',
                'trending_arrivals' => 'Hàng mới xu hướng',
                'more_to_discover' => 'Khám phá thêm',
                'shop_all_now' => 'Mua tất cả ngay',
                'best_seller_weeks' => 'Bán chạy nhất tuần này',
                'the_review_are_in' => 'Đánh giá đã có',
                'our_blog_news' => 'Blog & Tin tức',
                'discover_more' => 'Khám phá thêm',
            ],
            'fr' => [
                'popular_on_shofy' => 'Populaire sur la boutique Shofy.',
                'shop_by_category' => 'Acheter par catégorie',
                'customer_favorite' => 'Produits de style préférés des clients',
                'all_product_shop' => 'Tous les produits',
                'this_weeks_featured' => 'En vedette cette semaine',
                'trending_arrivals' => 'Nouveautés tendance',
                'more_to_discover' => 'Plus à découvrir',
                'shop_all_now' => 'Tout acheter maintenant',
                'best_seller_weeks' => 'Meilleure vente de la semaine',
                'the_review_are_in' => 'Les avis sont là',
                'our_blog_news' => 'Notre blog et actualités',
                'discover_more' => 'En savoir plus',
            ],
            'id' => [
                'popular_on_shofy' => 'Populer di toko Shofy.',
                'shop_by_category' => 'Belanja berdasarkan kategori',
                'customer_favorite' => 'Produk gaya favorit pelanggan',
                'all_product_shop' => 'Semua produk',
                'this_weeks_featured' => 'Unggulan minggu ini',
                'trending_arrivals' => 'Produk baru tren',
                'more_to_discover' => 'Lebih banyak untuk dijelajahi',
                'shop_all_now' => 'Belanja semua sekarang',
                'best_seller_weeks' => 'Terlaris minggu ini',
                'the_review_are_in' => 'Ulasan sudah masuk',
                'our_blog_news' => 'Blog & berita kami',
                'discover_more' => 'Temukan lebih banyak',
            ],
            'tr' => [
                'popular_on_shofy' => 'Shofy mağazasında popüler.',
                'shop_by_category' => 'Kategoriye göre alışveriş',
                'customer_favorite' => 'Müşteri favori stil ürünleri',
                'all_product_shop' => 'Tüm ürünler',
                'this_weeks_featured' => 'Bu haftanın öne çıkanları',
                'trending_arrivals' => 'Trend yeni gelenler',
                'more_to_discover' => 'Keşfedilecek daha fazlası',
                'shop_all_now' => 'Hepsini şimdi al',
                'best_seller_weeks' => 'Bu haftanın en çok satanları',
                'the_review_are_in' => 'Değerlendirmeler geldi',
                'our_blog_news' => 'Blog ve haberlerimiz',
                'discover_more' => 'Daha fazla keşfet',
            ],
        ];

        return $translations[$locale] ?? $translations['vi'];
    }

    protected function getBeautyPageTranslations(string $locale): array
    {
        $translations = [
            'ar' => [
                'discover_our_products' => 'اكتشفي منتجاتنا',
                'product_collection' => 'مجموعة المنتجات',
                'shop_all_products' => 'تسوق جميع المنتجات',
                'best_sellers_beauty' => 'الأكثر مبيعاً في الجمال',
                'shop_by_category' => 'تسوق حسب الفئة',
                'enjoy_best_quality' => 'استمتعي بأفضل جودة',
                'best_seller_weeks' => 'الأكثر مبيعاً هذا الأسبوع',
                'what_clients_say' => 'ماذا يقول عملاؤنا',
                'customers_review' => 'تقييمات العملاء',
            ],
            'vi' => [
                'discover_our_products' => 'Khám phá sản phẩm của chúng tôi',
                'product_collection' => 'Bộ sưu tập sản phẩm',
                'shop_all_products' => 'Mua tất cả sản phẩm',
                'best_sellers_beauty' => 'Bán chạy nhất về làm đẹp',
                'shop_by_category' => 'Mua sắm theo danh mục',
                'enjoy_best_quality' => 'Tận hưởng chất lượng tốt nhất',
                'best_seller_weeks' => 'Bán chạy nhất tuần này',
                'what_clients_say' => 'Khách hàng nói gì',
                'customers_review' => 'Đánh giá của khách hàng',
            ],
            'fr' => [
                'discover_our_products' => 'Découvrez nos produits',
                'product_collection' => 'Collection de produits',
                'shop_all_products' => 'Voir tous les produits',
                'best_sellers_beauty' => 'Meilleures ventes beauté',
                'shop_by_category' => 'Acheter par catégorie',
                'enjoy_best_quality' => 'Profitez de la meilleure qualité',
                'best_seller_weeks' => 'Meilleure vente de la semaine',
                'what_clients_say' => 'Ce que disent nos clients',
                'customers_review' => 'Avis des clients',
            ],
            'id' => [
                'discover_our_products' => 'Temukan produk kami',
                'product_collection' => 'Koleksi Produk',
                'shop_all_products' => 'Belanja semua produk',
                'best_sellers_beauty' => 'Terlaris di kecantikan',
                'shop_by_category' => 'Belanja berdasarkan kategori',
                'enjoy_best_quality' => 'Nikmati kualitas terbaik',
                'best_seller_weeks' => 'Terlaris minggu ini',
                'what_clients_say' => 'Apa kata klien kami',
                'customers_review' => 'Ulasan pelanggan',
            ],
            'tr' => [
                'discover_our_products' => 'Ürünlerimizi keşfedin',
                'product_collection' => 'Ürün Koleksiyonu',
                'shop_all_products' => 'Tüm ürünleri incele',
                'best_sellers_beauty' => 'Güzellikte en çok satanlar',
                'shop_by_category' => 'Kategoriye göre alışveriş',
                'enjoy_best_quality' => 'En iyi kaliteden yararlanın',
                'best_seller_weeks' => 'Bu haftanın en çok satanları',
                'what_clients_say' => 'Müşterilerimiz ne diyor',
                'customers_review' => 'Müşteri yorumları',
            ],
        ];

        return $translations[$locale] ?? $translations['vi'];
    }

    protected function getJewelryPageTranslations(string $locale): array
    {
        $translations = [
            'ar' => [
                'unity_collection' => 'مجموعة يونيتي',
                'shop_limited_edition' => 'تسوق تعاونات الإصدار المحدود',
                'about_description' => 'لوريم إيبسوم دولور سيت أميت، كونسيكتيتور أديبيسينغ إيليت. كراس فيل مي كوام. فوسي فيهيكولا فيتاي ماوريس سيت أميت تيمبور.',
                'contact_us_label' => 'اتصل بنا',
                'this_weeks_featured' => 'مميز هذا الأسبوع',
                'shop_by_category' => 'تسوق حسب الفئة',
                'discover_products' => 'اكتشفي منتجاتنا',
                'product_collection' => 'مجموعة المنتجات',
                'top_sellers_dress' => 'الأكثر مبيعاً في الفساتين لكِ',
                'best_seller_weeks' => 'الأكثر مبيعاً هذا الأسبوع',
                'trends_image_feed' => 'اتجاهات الصور',
                'gallery_subtitle' => 'بعد عدة أشهر من تصميم وتطوير متجر إلكتروني حديث',
            ],
            'vi' => [
                'unity_collection' => 'Bộ sưu tập Unity',
                'shop_limited_edition' => 'Mua sắm phiên bản giới hạn',
                'about_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras vel mi quam. Fusce vehicula vitae mauris sit amet tempor.',
                'contact_us_label' => 'Liên hệ chúng tôi',
                'this_weeks_featured' => 'Nổi bật tuần này',
                'shop_by_category' => 'Mua sắm theo danh mục',
                'discover_products' => 'Khám phá sản phẩm của chúng tôi',
                'product_collection' => 'Bộ sưu tập sản phẩm',
                'top_sellers_dress' => 'Bán chạy nhất về váy cho bạn',
                'best_seller_weeks' => 'Bán chạy nhất tuần này',
                'trends_image_feed' => 'Xu hướng trên hình ảnh',
                'gallery_subtitle' => 'Sau nhiều tháng thiết kế và phát triển cửa hàng trực tuyến hiện đại',
            ],
            'fr' => [
                'unity_collection' => 'Collection Unity',
                'shop_limited_edition' => 'Découvrez nos collaborations en édition limitée',
                'about_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras vel mi quam. Fusce vehicula vitae mauris sit amet tempor.',
                'contact_us_label' => 'Contactez-nous',
                'this_weeks_featured' => 'En vedette cette semaine',
                'shop_by_category' => 'Acheter par catégorie',
                'discover_products' => 'Découvrez nos produits',
                'product_collection' => 'Collection de produits',
                'top_sellers_dress' => 'Meilleures ventes de robes pour vous',
                'best_seller_weeks' => 'Meilleure vente de la semaine',
                'trends_image_feed' => 'Tendances en images',
                'gallery_subtitle' => "Après plusieurs mois de conception et développement d'un détaillant en ligne moderne",
            ],
            'id' => [
                'unity_collection' => 'Koleksi Unity',
                'shop_limited_edition' => 'Belanja kolaborasi edisi terbatas',
                'about_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras vel mi quam. Fusce vehicula vitae mauris sit amet tempor.',
                'contact_us_label' => 'Hubungi kami',
                'this_weeks_featured' => 'Unggulan minggu ini',
                'shop_by_category' => 'Belanja berdasarkan kategori',
                'discover_products' => 'Temukan produk kami',
                'product_collection' => 'Koleksi Produk',
                'top_sellers_dress' => 'Terlaris di gaun untuk Anda',
                'best_seller_weeks' => 'Terlaris minggu ini',
                'trends_image_feed' => 'Tren di feed gambar',
                'gallery_subtitle' => 'Setelah berbulan-bulan desain dan pengembangan toko online modern',
            ],
            'tr' => [
                'unity_collection' => 'Unity Koleksiyonu',
                'shop_limited_edition' => 'Sınırlı sayıda işbirliklerimizi keşfedin',
                'about_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras vel mi quam. Fusce vehicula vitae mauris sit amet tempor.',
                'contact_us_label' => 'Bize ulaşın',
                'this_weeks_featured' => 'Bu haftanın öne çıkanları',
                'shop_by_category' => 'Kategoriye göre alışveriş',
                'discover_products' => 'Ürünlerimizi keşfedin',
                'product_collection' => 'Ürün Koleksiyonu',
                'top_sellers_dress' => 'Sizin için en çok satılan elbiseler',
                'best_seller_weeks' => 'Bu haftanın en çok satanları',
                'trends_image_feed' => 'Görsel akışında trendler',
                'gallery_subtitle' => 'Aylarca süren modern çevrimiçi mağaza tasarımı ve geliştirmesinden sonra',
            ],
        ];

        return $translations[$locale] ?? $translations['vi'];
    }

    protected function getGroceryPageTranslations(string $locale): array
    {
        $translations = [
            'ar' => [
                'popular_on_shofy' => 'شائع في متجر Shofy.',
                'shop_by_category' => 'تسوق حسب الفئة',
                'all_product_shop' => 'جميع المنتجات',
                'grab_best_offer' => 'احصل على أفضل عرض هذا الأسبوع!',
                'best_deals_week' => 'أفضل عروض الأسبوع!',
                'happy_customers' => 'عملاؤنا السعداء',
                'customer_reviews' => 'تقييمات العملاء',
                'bestsellers_week' => 'الأكثر مبيعاً هذا الأسبوع',
                'more_to_discover' => 'المزيد للاكتشاف',
                'flexible_delivery' => 'توصيل مرن',
                'money_back_100' => 'استرداد 100% من المال',
                'secure_payment' => 'دفع آمن',
                'hour_support_24' => 'دعم 24 ساعة',
                'get_app_groceries' => 'حمّل التطبيق واحصل على بقالتك من المنزل',
            ],
            'vi' => [
                'popular_on_shofy' => 'Phổ biến trên cửa hàng Shofy.',
                'shop_by_category' => 'Mua sắm theo danh mục',
                'all_product_shop' => 'Tất cả sản phẩm',
                'grab_best_offer' => 'Nắm bắt ưu đãi tốt nhất tuần này!',
                'best_deals_week' => 'Ưu đãi tốt nhất tuần!',
                'happy_customers' => 'Khách hàng hài lòng',
                'customer_reviews' => 'Đánh giá của khách hàng',
                'bestsellers_week' => 'Bán chạy nhất tuần',
                'more_to_discover' => 'Khám phá thêm',
                'flexible_delivery' => 'Giao hàng linh hoạt',
                'money_back_100' => 'Hoàn tiền 100%',
                'secure_payment' => 'Thanh toán an toàn',
                'hour_support_24' => 'Hỗ trợ 24 giờ',
                'get_app_groceries' => 'Tải ứng dụng và đặt hàng tạp hóa tại nhà',
            ],
            'fr' => [
                'popular_on_shofy' => 'Populaire sur la boutique Shofy.',
                'shop_by_category' => 'Acheter par catégorie',
                'all_product_shop' => 'Tous les produits',
                'grab_best_offer' => 'Profitez de la meilleure offre de la semaine !',
                'best_deals_week' => 'Meilleures offres de la semaine !',
                'happy_customers' => 'Nos clients satisfaits',
                'customer_reviews' => 'Avis des clients',
                'bestsellers_week' => 'Meilleures ventes de la semaine',
                'more_to_discover' => 'Plus à découvrir',
                'flexible_delivery' => 'Livraison flexible',
                'money_back_100' => 'Remboursement 100%',
                'secure_payment' => 'Paiement sécurisé',
                'hour_support_24' => 'Support 24h',
                'get_app_groceries' => "Téléchargez l'appli et faites vos courses depuis chez vous",
            ],
            'id' => [
                'popular_on_shofy' => 'Populer di toko Shofy.',
                'shop_by_category' => 'Belanja berdasarkan kategori',
                'all_product_shop' => 'Semua produk',
                'grab_best_offer' => 'Dapatkan penawaran terbaik minggu ini!',
                'best_deals_week' => 'Penawaran terbaik minggu ini!',
                'happy_customers' => 'Pelanggan kami yang puas',
                'customer_reviews' => 'Ulasan pelanggan',
                'bestsellers_week' => 'Terlaris minggu ini',
                'more_to_discover' => 'Lebih banyak untuk dijelajahi',
                'flexible_delivery' => 'Pengiriman fleksibel',
                'money_back_100' => 'Uang kembali 100%',
                'secure_payment' => 'Pembayaran aman',
                'hour_support_24' => 'Dukungan 24 jam',
                'get_app_groceries' => 'Unduh aplikasi dan belanja bahan makanan dari rumah',
            ],
            'tr' => [
                'popular_on_shofy' => 'Shofy mağazasında popüler.',
                'shop_by_category' => 'Kategoriye göre alışveriş',
                'all_product_shop' => 'Tüm ürünler',
                'grab_best_offer' => 'Bu haftanın en iyi teklifini yakalayın!',
                'best_deals_week' => 'Haftanın en iyi fırsatları!',
                'happy_customers' => 'Mutlu müşterilerimiz',
                'customer_reviews' => 'Müşteri yorumları',
                'bestsellers_week' => 'Haftanın en çok satanları',
                'more_to_discover' => 'Keşfedilecek daha fazlası',
                'flexible_delivery' => 'Esnek teslimat',
                'money_back_100' => '%100 para iadesi',
                'secure_payment' => 'Güvenli ödeme',
                'hour_support_24' => '24 saat destek',
                'get_app_groceries' => 'Uygulamayı indirin ve bakkaliyenizi evden alın',
            ],
        ];

        return $translations[$locale] ?? $translations['vi'];
    }

    protected function getSliderTranslations(string $themeKey): array
    {
        return match ($themeKey) {
            'fashion', 'beauty' => $this->getFashionBeautySliderTranslations(),
            'jewelry' => $this->getJewelrySliderTranslations(),
            'grocery' => $this->getGrocerySliderTranslations(),
            default => $this->getMainSliderTranslations(),
        };
    }

    protected function getMainSliderTranslations(): array
    {
        return [
            'ar' => [
                'name' => 'سلايدر الرئيسية',
                'items' => [
                    [
                        'title' => 'أفضل مجموعة أجهزة لوحية 2023',
                        'description' => 'عرض حصري <span>-35%</span> خصم هذا الأسبوع',
                        'subtitle' => 'يبدأ من <b>$274.00</b>',
                        'button_label' => 'تسوق الآن',
                    ],
                    [
                        'title' => 'أفضل مجموعة حواسيب محمولة 2023',
                        'description' => 'عرض حصري <span>-10%</span> خصم هذا الأسبوع',
                        'subtitle' => 'يبدأ من <b>$999.00</b>',
                        'button_label' => 'تسوق الآن',
                    ],
                    [
                        'title' => 'أفضل مجموعة هواتف 2023',
                        'description' => 'عرض حصري <span>-10%</span> خصم هذا الأسبوع',
                        'subtitle' => 'يبدأ من <b>$999.00</b>',
                        'button_label' => 'تسوق الآن',
                    ],
                ],
            ],
            'vi' => [
                'name' => 'Slider trang chủ',
                'items' => [
                    [
                        'title' => 'Bộ sưu tập máy tính bảng tốt nhất 2023',
                        'description' => 'Ưu đãi độc quyền <span>-35%</span> giảm giá tuần này',
                        'subtitle' => 'Giá từ <b>$274.00</b>',
                        'button_label' => 'Mua ngay',
                    ],
                    [
                        'title' => 'Bộ sưu tập laptop tốt nhất 2023',
                        'description' => 'Ưu đãi độc quyền <span>-10%</span> giảm giá tuần này',
                        'subtitle' => 'Giá từ <b>$999.00</b>',
                        'button_label' => 'Mua ngay',
                    ],
                    [
                        'title' => 'Bộ sưu tập điện thoại tốt nhất 2023',
                        'description' => 'Ưu đãi độc quyền <span>-10%</span> giảm giá tuần này',
                        'subtitle' => 'Giá từ <b>$999.00</b>',
                        'button_label' => 'Mua ngay',
                    ],
                ],
            ],
            'fr' => [
                'name' => 'Slider accueil',
                'items' => [
                    [
                        'title' => 'La meilleure collection de tablettes 2023',
                        'description' => 'Offre exclusive <span>-35%</span> de réduction cette semaine',
                        'subtitle' => 'À partir de <b>$274.00</b>',
                        'button_label' => 'Acheter',
                    ],
                    [
                        'title' => 'La meilleure collection de notebooks 2023',
                        'description' => 'Offre exclusive <span>-10%</span> de réduction cette semaine',
                        'subtitle' => 'À partir de <b>$999.00</b>',
                        'button_label' => 'Acheter',
                    ],
                    [
                        'title' => 'La meilleure collection de téléphones 2023',
                        'description' => 'Offre exclusive <span>-10%</span> de réduction cette semaine',
                        'subtitle' => 'À partir de <b>$999.00</b>',
                        'button_label' => 'Acheter',
                    ],
                ],
            ],
            'id' => [
                'name' => 'Slider beranda',
                'items' => [
                    [
                        'title' => 'Koleksi tablet terbaik 2023',
                        'description' => 'Penawaran eksklusif <span>-35%</span> diskon minggu ini',
                        'subtitle' => 'Mulai dari <b>$274.00</b>',
                        'button_label' => 'Belanja',
                    ],
                    [
                        'title' => 'Koleksi notebook terbaik 2023',
                        'description' => 'Penawaran eksklusif <span>-10%</span> diskon minggu ini',
                        'subtitle' => 'Mulai dari <b>$999.00</b>',
                        'button_label' => 'Belanja',
                    ],
                    [
                        'title' => 'Koleksi ponsel terbaik 2023',
                        'description' => 'Penawaran eksklusif <span>-10%</span> diskon minggu ini',
                        'subtitle' => 'Mulai dari <b>$999.00</b>',
                        'button_label' => 'Belanja',
                    ],
                ],
            ],
            'tr' => [
                'name' => 'Ana sayfa slider',
                'items' => [
                    [
                        'title' => 'En iyi tablet koleksiyonu 2023',
                        'description' => 'Özel teklif <span>-35%</span> bu hafta indirimli',
                        'subtitle' => '<b>$274.00</b>\'dan başlayan',
                        'button_label' => 'Alışveriş',
                    ],
                    [
                        'title' => 'En iyi notebook koleksiyonu 2023',
                        'description' => 'Özel teklif <span>-10%</span> bu hafta indirimli',
                        'subtitle' => '<b>$999.00</b>\'dan başlayan',
                        'button_label' => 'Alışveriş',
                    ],
                    [
                        'title' => 'En iyi telefon koleksiyonu 2023',
                        'description' => 'Özel teklif <span>-10%</span> bu hafta indirimli',
                        'subtitle' => '<b>$999.00</b>\'dan başlayan',
                        'button_label' => 'Alışveriş',
                    ],
                ],
            ],
        ];
    }

    protected function getFashionBeautySliderTranslations(): array
    {
        return [
            'ar' => [
                'name' => 'سلايدر الرئيسية',
                'items' => [
                    ['title' => 'مجموعة الملابس', 'description' => 'وصل حديثاً 2023', 'button_label' => 'تسوق المجموعة'],
                    ['title' => 'مجموعة الصيف', 'description' => 'الأكثر مبيعاً 2023', 'button_label' => 'تسوق المجموعة'],
                    ['title' => 'تصاميم رائعة جديدة', 'description' => 'وصل الشتاء', 'button_label' => 'تسوق المجموعة'],
                ],
            ],
            'vi' => [
                'name' => 'Slider trang chủ',
                'items' => [
                    ['title' => 'Bộ sưu tập quần áo', 'description' => 'Hàng mới về 2023', 'button_label' => 'Mua bộ sưu tập'],
                    ['title' => 'Bộ sưu tập mùa hè', 'description' => 'Bán chạy nhất 2023', 'button_label' => 'Mua bộ sưu tập'],
                    ['title' => 'Thiết kế mới tuyệt vời', 'description' => 'Mùa đông đã đến', 'button_label' => 'Mua bộ sưu tập'],
                ],
            ],
            'fr' => [
                'name' => 'Slider accueil',
                'items' => [
                    ['title' => 'La collection de vêtements', 'description' => 'Nouveautés 2023', 'button_label' => 'Voir la collection'],
                    ['title' => "La collection d'été", 'description' => 'Meilleures ventes 2023', 'button_label' => 'Voir la collection'],
                    ['title' => 'Nouveaux designs incroyables', 'description' => "L'hiver est arrivé", 'button_label' => 'Voir la collection'],
                ],
            ],
            'id' => [
                'name' => 'Slider beranda',
                'items' => [
                    ['title' => 'Koleksi Pakaian', 'description' => 'Produk Baru 2023', 'button_label' => 'Belanja Koleksi'],
                    ['title' => 'Koleksi Musim Panas', 'description' => 'Terlaris 2023', 'button_label' => 'Belanja Koleksi'],
                    ['title' => 'Desain Baru yang Menakjubkan', 'description' => 'Musim Dingin Tiba', 'button_label' => 'Belanja Koleksi'],
                ],
            ],
            'tr' => [
                'name' => 'Ana sayfa slider',
                'items' => [
                    ['title' => 'Giyim Koleksiyonu', 'description' => 'Yeni Gelenler 2023', 'button_label' => 'Koleksiyonu İncele'],
                    ['title' => 'Yaz Koleksiyonu', 'description' => 'En Çok Satanlar 2023', 'button_label' => 'Koleksiyonu İncele'],
                    ['title' => 'Muhteşem Yeni Tasarımlar', 'description' => 'Kış Geldi', 'button_label' => 'Koleksiyonu İncele'],
                ],
            ],
        ];
    }

    protected function getJewelrySliderTranslations(): array
    {
        return [
            'ar' => [
                'name' => 'سلايدر الرئيسية',
                'items' => [
                    ['title' => 'تألقي ببريق', 'description' => 'الأصلي', 'button_label' => 'اكتشف الآن'],
                    ['title' => 'تصميم إبداعي', 'description' => 'الأصلي', 'button_label' => 'اكتشف الآن'],
                    ['title' => 'مطلي بالذهب', 'description' => 'الأصلي', 'button_label' => 'اكتشف الآن'],
                    ['title' => 'أشكال فريدة', 'description' => 'الأصلي', 'button_label' => 'اكتشف الآن'],
                ],
            ],
            'vi' => [
                'name' => 'Slider trang chủ',
                'items' => [
                    ['title' => 'Tỏa sáng rực rỡ', 'description' => 'Nguyên bản', 'button_label' => 'Khám phá ngay'],
                    ['title' => 'Thiết kế sáng tạo', 'description' => 'Nguyên bản', 'button_label' => 'Khám phá ngay'],
                    ['title' => 'Mạ vàng', 'description' => 'Nguyên bản', 'button_label' => 'Khám phá ngay'],
                    ['title' => 'Hình dạng độc đáo', 'description' => 'Nguyên bản', 'button_label' => 'Khám phá ngay'],
                ],
            ],
            'fr' => [
                'name' => 'Slider accueil',
                'items' => [
                    ['title' => 'Brillez de mille feux', 'description' => "L'original", 'button_label' => 'Découvrir maintenant'],
                    ['title' => 'Design créatif', 'description' => "L'original", 'button_label' => 'Découvrir maintenant'],
                    ['title' => 'Plaqué or', 'description' => "L'original", 'button_label' => 'Découvrir maintenant'],
                    ['title' => 'Formes uniques', 'description' => "L'original", 'button_label' => 'Découvrir maintenant'],
                ],
            ],
            'id' => [
                'name' => 'Slider beranda',
                'items' => [
                    ['title' => 'Bersinar terang', 'description' => 'Yang asli', 'button_label' => 'Temukan Sekarang'],
                    ['title' => 'Desain Kreatif', 'description' => 'Yang asli', 'button_label' => 'Temukan Sekarang'],
                    ['title' => 'Berlapis Emas', 'description' => 'Yang asli', 'button_label' => 'Temukan Sekarang'],
                    ['title' => 'Bentuk Unik', 'description' => 'Yang asli', 'button_label' => 'Temukan Sekarang'],
                ],
            ],
            'tr' => [
                'name' => 'Ana sayfa slider',
                'items' => [
                    ['title' => 'Parlak ışılda', 'description' => 'Orijinal', 'button_label' => 'Şimdi Keşfet'],
                    ['title' => 'Yaratıcı Tasarım', 'description' => 'Orijinal', 'button_label' => 'Şimdi Keşfet'],
                    ['title' => 'Altın Kaplama', 'description' => 'Orijinal', 'button_label' => 'Şimdi Keşfet'],
                    ['title' => 'Benzersiz Şekiller', 'description' => 'Orijinal', 'button_label' => 'Şimdi Keşfet'],
                ],
            ],
        ];
    }

    protected function getGrocerySliderTranslations(): array
    {
        return [
            'ar' => [
                'name' => 'سلايدر الرئيسية',
                'items' => [
                    ['title' => 'متجر البقالة <br> عبر الإنترنت', 'button_label' => 'تسوق الآن'],
                ],
            ],
            'vi' => [
                'name' => 'Slider trang chủ',
                'items' => [
                    ['title' => 'Cửa hàng tạp hóa <br> trực tuyến', 'button_label' => 'Mua ngay'],
                ],
            ],
            'fr' => [
                'name' => 'Slider accueil',
                'items' => [
                    ['title' => "L'épicerie <br> en ligne", 'button_label' => 'Acheter'],
                ],
            ],
            'id' => [
                'name' => 'Slider beranda',
                'items' => [
                    ['title' => 'Toko Kelontong <br> Online', 'button_label' => 'Belanja'],
                ],
            ],
            'tr' => [
                'name' => 'Ana sayfa slider',
                'items' => [
                    ['title' => 'Online <br> Market', 'button_label' => 'Hemen Alışveriş Yap'],
                ],
            ],
        ];
    }
}
