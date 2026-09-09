<?php

namespace Botble\Blog\Database\Traits;

use Botble\ACL\Models\User;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Models\Tag;
use Botble\Setting\Facades\Setting;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HasBlogSeeder
{
    protected Collection $userIds;

    protected function truncateBlogTranslations(): void
    {
        DB::table('categories_translations')->truncate();
        DB::table('tags_translations')->truncate();
        DB::table('posts_translations')->truncate();
    }

    /**
     * Create blog categories with translations.
     *
     * @param array $categories Array with 'name', optional 'translations' key
     */
    protected function createBlogCategoriesWithTranslations(array $categories, bool $truncate = true): void
    {
        if ($truncate) {
            Category::query()->truncate();
        }

        foreach ($categories as $index => $item) {
            $translations = $item['translations'] ?? [];
            unset($item['translations']);

            $item['description'] ??= 'Explore our collection of articles and insights in this category.';
            $item['is_featured'] ??= ! isset($item['parent_id']) && $index != 0;
            $item['parent_id'] ??= 0;

            $category = $this->createBlogCategory(Arr::except($item, 'children'));

            foreach ($translations as $locale => $translation) {
                DB::table('categories_translations')->insert([
                    'lang_code' => $locale,
                    'categories_id' => $category->getKey(),
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                ]);
            }

            if (Arr::has($item, 'children')) {
                foreach (Arr::get($item, 'children', []) as $child) {
                    $child['parent_id'] = $category->getKey();

                    $childTranslations = $child['translations'] ?? [];
                    unset($child['translations']);

                    $childCategory = $this->createBlogCategory($child);

                    foreach ($childTranslations as $locale => $translation) {
                        DB::table('categories_translations')->insert([
                            'lang_code' => $locale,
                            'categories_id' => $childCategory->getKey(),
                            'name' => $translation['name'],
                            'description' => $translation['description'] ?? null,
                        ]);
                    }
                }
            }

            $this->createMetadata($category, $item);
        }
    }

    /**
     * Create blog tags with translations.
     *
     * @param array $tags Array with 'name', optional 'translations' key
     */
    protected function createBlogTagsWithTranslations(array $tags, bool $truncate = true): void
    {
        if ($truncate) {
            Tag::query()->truncate();
        }

        $userIds = $this->getUserIds();

        foreach ($tags as $item) {
            $translations = $item['translations'] ?? [];
            unset($item['translations']);

            $item['author_id'] ??= $userIds->random();
            $item['author_type'] ??= User::class;

            /**
             * @var Tag $tag
             */
            $tag = Tag::query()->create(Arr::except($item, ['metadata']));

            SlugHelper::createSlug($tag);

            foreach ($translations as $locale => $translation) {
                DB::table('tags_translations')->insert([
                    'lang_code' => $locale,
                    'tags_id' => $tag->getKey(),
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                ]);
            }

            $this->createMetadata($tag, $item);
        }
    }

    /**
     * Create blog posts with translations.
     *
     * @param array $data Array with post data, optional 'translations' key
     */
    protected function createBlogPostsWithTranslations(array $data, bool $truncate = true): array
    {
        if ($truncate) {
            Post::query()->truncate();
            DB::table('post_categories')->truncate();
            DB::table('post_tags')->truncate();
        }

        $categoryIds = Category::query()->pluck('id');
        $tagIds = Tag::query()->pluck('id');
        $userIds = $this->getUserIds();

        $posts = [];

        foreach ($data as $index => $item) {
            $translations = $item['translations'] ?? [];
            unset($item['translations']);

            $item['views'] ??= rand(100, 2500);
            $item['description'] ??= 'Discover the latest insights, trends, and expert analysis in this comprehensive article.';
            $item['is_featured'] ??= $index < 5;

            if (! empty($item['content'])) {
                $item['content'] = $this->removeBaseUrlFromString((string) $item['content']);
            } else {
                $item['content'] = 'This article provides an in-depth exploration of the topic.';
            }

            $item['author_id'] ??= $userIds->random();
            $item['author_type'] ??= User::class;

            /**
             * @var Post $post
             */
            $post = Post::query()->create(Arr::except($item, ['metadata']));

            $post->categories()->sync(array_unique([
                $categoryIds->random(),
                $categoryIds->random(),
            ]));

            $post->tags()->sync(array_unique([
                $tagIds->random(),
                $tagIds->random(),
                $tagIds->random(),
            ]));

            SlugHelper::createSlug($post);

            foreach ($translations as $locale => $translation) {
                DB::table('posts_translations')->insert([
                    'lang_code' => $locale,
                    'posts_id' => $post->getKey(),
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                    'content' => $translation['content'] ?? null,
                ]);
            }

            $this->createMetadata($post, $item);

            $posts[] = $post;
        }

        return $posts;
    }

    protected function getUserIds(): Collection
    {
        if (! isset($this->userIds)) {
            $this->userIds = User::query()->pluck('id');
        }

        return $this->userIds;
    }

    protected function createBlogCategories(array $categories, bool $truncate = true): void
    {
        if ($truncate) {
            Category::query()->truncate();
        }

        foreach ($categories as $index => $item) {
            $item['description'] ??= 'Explore our collection of articles and insights in this category.';
            $item['is_featured'] ??= ! isset($item['parent_id']) && $index != 0;
            $item['parent_id'] ??= 0;

            $category = $this->createBlogCategory(Arr::except($item, 'children'));

            if (Arr::has($item, 'children')) {
                foreach (Arr::get($item, 'children', []) as $child) {
                    $child['parent_id'] = $category->getKey();

                    $this->createBlogCategory($child);
                }
            }

            $this->createMetadata($category, $item);
        }
    }

    protected function createBlogTags(array $tags, bool $truncate = true): void
    {
        if ($truncate) {
            Tag::query()->truncate();
        }

        $userIds = $this->getUserIds();

        foreach ($tags as $item) {
            $item['author_id'] ??= $userIds->random();
            $item['author_type'] ??= User::class;

            /**
             * @var Tag $tag
             */
            $tag = Tag::query()->create(Arr::except($item, ['metadata']));

            SlugHelper::createSlug($tag);

            $this->createMetadata($tag, $item);
        }
    }

    protected function createBlogPosts(array $data, bool $truncate = true): array
    {
        if ($truncate) {
            Post::query()->truncate();
            DB::table('post_categories')->truncate();
            DB::table('post_tags')->truncate();
        }

        $categoryIds = Category::query()->pluck('id');
        $tagIds = Tag::query()->pluck('id');
        $userIds = $this->getUserIds();

        $posts = [];

        foreach ($data as $index => $item) {
            $item['views'] ??= rand(100, 2500);
            $item['description'] ??= 'Discover the latest insights, trends, and expert analysis in this comprehensive article that covers key aspects of the topic.';
            $item['is_featured'] ??= $index < 5;

            if (! empty($item['content'])) {
                $item['content'] = $this->removeBaseUrlFromString((string) $item['content']);
            } else {
                $item['content'] = 'This article provides an in-depth exploration of the topic, offering valuable insights and practical information for readers seeking to expand their knowledge.';
            }

            $item['author_id'] ??= $userIds->random();
            $item['author_type'] ??= User::class;

            /**
             * @var Post $post
             */
            $post = Post::query()->create(Arr::except($item, ['metadata']));

            $post->categories()->sync(array_unique([
                $categoryIds->random(),
                $categoryIds->random(),
            ]));

            $post->tags()->sync(array_unique([
                $tagIds->random(),
                $tagIds->random(),
                $tagIds->random(),
            ]));

            SlugHelper::createSlug($post);

            $this->createMetadata($post, $item);

            $posts[] = $post;
        }

        return $posts;
    }

    protected function getCategoryId(string $name): int|string
    {
        return Category::query()->where('name', $name)->value('id');
    }

    protected function createBlogCategory(array $item): Category
    {
        $userIds = $this->getUserIds();

        $item['author_id'] ??= $userIds->random();
        $item['author_type'] ??= User::class;

        /**
         * @var Category $category
         */
        $category = Category::query()->create(Arr::except($item, ['metadata']));

        SlugHelper::createSlug($category);

        $this->createMetadata($category, $item);

        return $category;
    }

    public function setPostSlugPrefix(string $prefix = 'blog'): void
    {
        Setting::set([
            SlugHelper::getPermalinkSettingKey(Post::class) => $prefix,
            SlugHelper::getPermalinkSettingKey(Category::class) => $prefix,
        ]);

        Setting::save();

        Slug::query()->where('reference_type', Post::class)->update(['prefix' => $prefix]);
        Slug::query()->where('reference_type', Category::class)->update(['prefix' => $prefix]);
    }
}
