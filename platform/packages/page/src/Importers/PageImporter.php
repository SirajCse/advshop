<?php

namespace Botble\Page\Importers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\DataSynchronize\Importer\ImportColumn;
use Botble\DataSynchronize\Importer\Importer;
use Botble\Media\Facades\RvMedia;
use Botble\Page\Models\Page;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageImporter extends Importer
{
    public function chunkSize(): int
    {
        return 50;
    }

    public function getLabel(): string
    {
        return trans('packages/page::pages.pages');
    }

    public function columns(): array
    {
        return [
            ImportColumn::make('name')
                ->rules(['required', 'string', 'max:120'], trans('packages/page::pages.import.rules.nullable_string_max', ['attribute' => 'Name', 'max' => 120])),
            ImportColumn::make('slug')
                ->rules(['nullable', 'string', 'max:250'], trans('packages/page::pages.import.rules.nullable_string_max', ['attribute' => 'Slug', 'max' => 250])),
            ImportColumn::make('description')
                ->rules(['nullable', 'string', 'max:400'], trans('packages/page::pages.import.rules.nullable_string_max', ['attribute' => 'Description', 'max' => 400])),
            ImportColumn::make('content')
                ->rules(['nullable', 'string', 'max:300000'], trans('packages/page::pages.import.rules.nullable_string_max', ['attribute' => 'Content', 'max' => '300,000'])),
            ImportColumn::make('image')
                ->rules(['nullable', 'string'], trans('packages/page::pages.import.rules.nullable_string', ['attribute' => 'Image'])),
            ImportColumn::make('template')
                ->rules(['nullable', 'string', 'max:60'], trans('packages/page::pages.import.rules.nullable_string_max', ['attribute' => 'Template', 'max' => 60])),
            ImportColumn::make('status')
                ->rules([Rule::in(BaseStatusEnum::values())], trans('packages/page::pages.import.rules.in', ['attribute' => 'Status', 'values' => implode(', ', BaseStatusEnum::values())])),
        ];
    }

    public function examples(): array
    {
        $pages = Page::query()
            ->take(3)
            ->with(['slugable'])
            ->get()
            ->map(function (Page $page) { // @phpstan-ignore-line
                return [
                    ...$page->toArray(),
                    'slug' => $page->slugable?->key,
                    'description' => Str::limit($page->description, 50),
                    'content' => Str::limit($page->content),
                    'image' => RvMedia::getImageUrl($page->image),
                ];
            });

        if ($pages->isNotEmpty()) {
            return $pages->all();
        }

        return [
            [
                'name' => 'About Us',
                'slug' => 'about-us',
                'description' => 'Learn more about our company, our mission, and our team.',
                'content' => 'Welcome to our company. We are dedicated to providing the best service to our customers.',
                'image' => 'https://via.placeholder.com/600x400',
                'template' => 'default',
                'status' => BaseStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Contact',
                'slug' => 'contact',
                'description' => 'Get in touch with us for any inquiries or support.',
                'content' => 'Feel free to reach out to us via email or phone. We are here to help.',
                'image' => 'https://via.placeholder.com/600x400',
                'template' => 'default',
                'status' => BaseStatusEnum::PUBLISHED,
            ],
            [
                'name' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'description' => 'Read our terms and conditions before using our services.',
                'content' => 'By using our services, you agree to the following terms and conditions.',
                'image' => '',
                'template' => 'default',
                'status' => BaseStatusEnum::DRAFT,
            ],
        ];
    }

    public function getValidateUrl(): string
    {
        return route('tools.data-synchronize.import.pages.validate');
    }

    public function getImportUrl(): string
    {
        return route('tools.data-synchronize.import.pages.store');
    }

    public function getDownloadExampleUrl(): ?string
    {
        return route('tools.data-synchronize.import.pages.download-example');
    }

    public function getExportUrl(): ?string
    {
        return Auth::user()->hasPermission('pages.export')
            ? route('tools.data-synchronize.export.pages.store')
            : null;
    }

    public function handle(array $data): int
    {
        $count = 0;

        foreach ($data as $row) {
            $slug = Arr::pull($row, 'slug');

            /** @var Page $page */
            $page = Page::query()->firstOrCreate([
                'name' => Arr::pull($row, 'name'),
            ], [
                ...$row,
                'image' => $this->resolveMediaImage($row['image'] ?? null, 'pages'),
                'user_id' => Auth::id(),
            ]);

            if ($page->wasRecentlyCreated) {
                SlugHelper::createSlug($page, $slug);

                $count++;
            }
        }

        return $count;
    }
}
