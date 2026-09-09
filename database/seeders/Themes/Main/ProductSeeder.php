<?php

namespace Database\Seeders\Themes\Main;

use Botble\Ecommerce\Database\Seeders\Traits\HasProductSeeder;
use Botble\Ecommerce\Enums\UpSellPriceType;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Models\Store;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductSeeder extends ThemeSeeder
{
    use HasProductSeeder;

    public function run(): void
    {
        $this->uploadFiles('products');
        $this->uploadFiles('video', 'main');

        $content = File::get(database_path("seeders/contents/product-content-{$this->getThemeName()}.html"));

        $images = $this->getFilesFromPath('products')
            ->map(fn ($item) => $this->filePath($item))
            ->all();

        $storeIds = Store::query()->pluck('id')->all();
        $descriptions = $this->getDescriptions();

        $this->createProducts(array_map(function ($item) use ($images, $content, $storeIds, $descriptions) {
            return [
                'name' => $item,
                'description' => Arr::random($descriptions),
                'content' => $content,
                'images' => Arr::random($images, rand(3, 8)),
                'store_id' => Arr::random($storeIds),
                'video_media' => Arr::random([
                    [
                        [
                            ['key' => 'file', 'value' => $this->filePath(sprintf('video/video-%d.mp4', $videoId = rand(1, 2)), 'main')],
                            ['key' => 'url', 'value' => null],
                            ['key' => 'thumbnail', 'value' => $this->filePath(sprintf('video/%d.jpg', $videoId), 'main')],
                        ],
                    ],
                    [],
                    [
                        [
                            ['key' => 'file', 'value' => null],
                            ['key' => 'url', 'value' => 'https://www.youtube.com/watch?v=6JYIGclVQdw'],
                            ['key' => 'thumbnail', 'value' => $this->filePath(sprintf('video/%d.jpg', rand(1, 2)), 'main')],
                        ],
                    ],
                ]),
            ];
        }, $this->getProducts()));

        $this->seedUpSaleProducts();
    }

    protected function seedUpSaleProducts(): void
    {
        DB::table('ec_product_up_sale_relations')->truncate();

        $products = Product::query()
            ->where('is_variation', false)
            ->pluck('id')
            ->all();

        if (count($products) < 3) {
            return;
        }

        $faker = $this->fake();

        foreach ($products as $productId) {
            $upSaleCount = $faker->numberBetween(1, 2);

            $excludeIds = [$productId];
            $upSaleData = [];

            for ($i = 0; $i < $upSaleCount; $i++) {
                $upSaleProductId = $this->getRandomProductId($products, $excludeIds);

                if (! $upSaleProductId) {
                    continue;
                }

                $excludeIds[] = $upSaleProductId;

                $priceType = $faker->randomElement([UpSellPriceType::FIXED, UpSellPriceType::PERCENT]);
                $price = $priceType === UpSellPriceType::PERCENT
                    ? $faker->randomElement([5, 10, 15, 20, 25])
                    : $faker->numberBetween(10, 100);

                $upSaleData[$upSaleProductId] = [
                    'price' => $price,
                    'price_type' => $priceType,
                    'apply_to_all_variations' => true,
                    'is_variant' => false,
                ];
            }

            if (! empty($upSaleData)) {
                /** @var Product|null $product */
                $product = Product::query()->find($productId);
                $product?->upSales()->sync($upSaleData);
            }
        }
    }

    protected function getRandomProductId(array $products, array $excludeIds): ?int
    {
        $availableProducts = array_diff($products, $excludeIds);

        if (empty($availableProducts)) {
            return null;
        }

        return Arr::random($availableProducts);
    }

    protected function getProducts(): array
    {
        return [
            'EcoTech Marine Radion XR30w G5 Pro LED Light Fixture',
            'Philips Hue White and Color Ambiance A19 LED Smart Bulb',
            'Samsung Galaxy Tab S7+ 12.4-Inch Android Tablet',
            'Apple MacBook Pro 16-Inch Laptop',
            'Sony WH-1000XM4 Wireless Noise-Canceling Headphones',
            'DJI Mavic Air 2 Drone',
            'GoPro HERO9 Black Action Camera',
            'Bose SoundLink Revolve+ Portable Bluetooth Speaker',
            'Nest Learning Thermostat (3rd Generation)',
            'Ring Video Doorbell Pro',
            'Amazon Echo Show 10 (3rd Gen)',
            'Samsung QN90A Neo QLED 4K Smart TV',
            'LG OLED C1 Series 4K Smart TV',
            'Sony X950H 4K Ultra HD Smart LED TV',
            'Apple Watch Series 7',
            'Fitbit Charge 5 Fitness Tracker',
            'Garmin Fenix 7X Sapphire Solar GPS Watch',
            'Microsoft Surface Pro 8',
            'Lenovo ThinkPad X1 Carbon Gen 9 Laptop',
            'HP Spectre x360 14-Inch Convertible Laptop',
            'Razer Blade 15 Advanced Gaming Laptop',
            'Alienware m15 R6 Gaming Laptop',
            'Corsair K95 RGB Platinum XT Mechanical Gaming Keyboard',
            'Logitech G Pro X Superlight Wireless Gaming Mouse',
            'SteelSeries Arctis Pro Wireless Gaming Headset',
            'Elgato Stream Deck XL',
            'Nintendo Switch OLED Model',
            'PlayStation 5 Console',
            'Xbox Series X Console',
            'Oculus Quest 2 VR Headset',
            'HTC Vive Cosmos Elite VR Headset',
            'Samsung Odyssey G9 49-Inch Curved Gaming Monitor',
            'LG UltraGear 27GN950-B 4K Gaming Monitor',
            'Acer Predator X38 Pbmiphzx 38-Inch Curved Gaming Monitor',
            'ASUS ROG Swift PG279QM 27-Inch Gaming Monitor',
            'BenQ EW3280U 32-Inch 4K HDR Entertainment Monitor',
            'Dell UltraSharp U2720Q 27-Inch 4K USB-C Monitor',
            'HP Z27k G3 4K USB-C Monitor',
            'LG 27UK850-W 27-Inch 4K UHD IPS Monitor',
            'Samsung Odyssey G7 32-Inch Curved Gaming Monitor',
            'Sony X900H 4K Ultra HD Smart LED TV',
            'TCL 6-Series 4K UHD Dolby Vision HDR QLED Roku Smart TV',
            'Vizio OLED65-H1 65-Inch 4K OLED Smart TV',
            'Hisense U8G Quantum Series 4K ULED Android TV',
            'LG C1 Series 4K OLED Smart TV',
            'Samsung QN85A Neo QLED 4K Smart TV',
            'Sony A90J 4K OLED Smart TV',
            'Apple TV 4K (2nd Generation)',
            'Roku Ultra 2020 Streaming Media Player',
            'Amazon Fire TV Stick 4K Max',
            'Google Chromecast with Google TV',
            'NVIDIA SHIELD TV Pro',
            'Sonos Beam Gen 2 Soundbar',
            'Bose Smart Soundbar 900',
            'JBL Bar 9.1 Soundbar with Dolby Atmos',
            'Sennheiser Ambeo Soundbar',
            'Sony HT-A9 Home Theater System',
            'Samsung Galaxy Buds Pro',
        ];
    }

    protected function getDescriptions(): array
    {
        return [
            'Experience premium quality and exceptional performance with this cutting-edge device. Engineered with advanced technology and precision craftsmanship, it delivers an outstanding user experience that exceeds expectations.',
            'Designed for those who demand excellence, this product combines innovative features with elegant aesthetics. The intuitive interface and responsive controls make it a joy to use, while the durable construction ensures long-lasting reliability.',
            'Transform your daily routine with this state-of-the-art solution. Featuring intelligent functionality and seamless connectivity, it integrates effortlessly into your lifestyle while delivering powerful performance when you need it most.',
            'Built to impress, this product showcases the perfect balance of form and function. Premium materials and meticulous attention to detail result in a device that not only performs brilliantly but also looks stunning in any setting.',
            'Unlock new possibilities with advanced features designed for modern life. Whether you\'re working, creating, or entertaining, this versatile device adapts to your needs with effortless ease and remarkable efficiency.',
            'Elevate your experience with industry-leading technology packed into an elegant design. Every component has been carefully selected and optimized to deliver superior performance, exceptional comfort, and unmatched durability.',
        ];
    }
}
