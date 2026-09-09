<?php

namespace Database\Seeders\Themes\Main;

use Botble\Gallery\Models\Gallery;
use Botble\Gallery\Models\GalleryMeta;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Arr;

class GallerySeeder extends ThemeSeeder
{
    public function run(): void
    {
        $this->uploadFiles('galleries');

        Gallery::query()->truncate();
        GalleryMeta::query()->truncate();

        $descriptions = $this->getDescriptions();

        $images = [];

        foreach (Arr::random(range(1, 5), rand(3, 5)) as $i) {
            $images[] = [
                'img' => $this->filePath("galleries/$i.jpg"),
                'description' => Arr::random($descriptions),
            ];
        }

        foreach ($this->getData() as $index => $item) {
            $gallery = Gallery::query()->create([
                'user_id' => 1,
                'name' => $item,
                'description' => Arr::random($descriptions),
                'image' => $this->filePath(sprintf('galleries/%d.jpg', $index + 1)),
                'is_featured' => true,
            ]);

            SlugHelper::createSlug($gallery);

            GalleryMeta::query()->create([
                'images' => $images,
                'reference_id' => $gallery->getKey(),
                'reference_type' => Gallery::class,
            ]);
        }
    }

    protected function getData(): array
    {
        return [
            'Perfect',
            'New Day',
            'Happy Day',
            'Nature',
            'Morning',
        ];
    }

    protected function getDescriptions(): array
    {
        return [
            'A stunning collection of images capturing the essence of modern design and natural beauty.',
            'Explore our curated gallery featuring exceptional photography and creative artistry.',
            'Discover breathtaking visuals that inspire and captivate the imagination.',
            'An artistic journey through colors, textures, and moments frozen in time.',
            'Premium quality images showcasing craftsmanship and attention to detail.',
        ];
    }
}
