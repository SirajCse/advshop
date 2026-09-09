<?php

namespace Botble\Blog\Listeners;

use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Models\Tag;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Botble\Theme\Facades\SiteMapManager;
use Illuminate\Support\Arr;

class RenderingSiteMapListener
{
    public function handle(RenderingSiteMapEvent $event): void
    {
        if ($key = $event->key) {
            switch ($key) {
                case 'blog-categories':
                    $categories = Category::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->select(['id', 'name', 'updated_at'])->latest()
                        ->get();

                    foreach ($categories as $category) {
                        SiteMapManager::add($category->url, $category->updated_at, '0.8');
                    }

                    break;
                case 'blog-tags':
                    $tags = Tag::query()
                        ->with('slugable')
                        ->wherePublished()->latest()
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($tags as $tag) {
                        SiteMapManager::add($tag->url, $tag->updated_at, '0.3', 'weekly');
                    }

                    break;
            }

            // Consolidated blog-posts handler with optional page-based pagination.
            // Matches: blog-posts, blog-posts-page-{N}
            if ($key === 'blog-posts' || preg_match('/^blog-posts-page-(\d+)$/', $key, $pageMatches)) {
                $itemsPerPage = SiteMapManager::getItemsPerPage();
                $page = isset($pageMatches[1]) ? max(1, (int) $pageMatches[1]) : 1;
                $offset = ($page - 1) * $itemsPerPage;

                $posts = Post::query()
                    ->wherePublished()
                    ->latest('updated_at')
                    ->select(['id', 'name', 'updated_at', 'created_at'])
                    ->with(['slugable'])
                    ->skip($offset)
                    ->take($itemsPerPage)
                    ->get();

                foreach ($posts as $post) {
                    if (! $post->slugable) {
                        continue;
                    }

                    SiteMapManager::add($post->url, $post->updated_at, '0.8');
                }

                return;
            }

            // Backward compatibility: legacy monthly archive URLs (blog-posts-YYYY-MM[-page-N]).
            // Old indexed URLs continue to resolve so search engines can re-discover from the new index.
            $paginationData = SiteMapManager::extractPaginationDataByPattern($key, 'blog-posts', 'monthly-archive');

            if ($paginationData) {
                $matches = $paginationData['matches'];
                $year = Arr::get($matches, 1);
                $month = Arr::get($matches, 2);

                if ($year && $month) {
                    $posts = Post::query()
                        ->wherePublished()
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->latest('updated_at')
                        ->select(['id', 'name', 'updated_at', 'created_at'])
                        ->with(['slugable'])
                        ->skip($paginationData['offset'])
                        ->take($paginationData['limit'])
                        ->get();

                    foreach ($posts as $post) {
                        if (! $post->slugable) {
                            continue;
                        }

                        SiteMapManager::add($post->url, $post->updated_at, '0.8');
                    }
                }
            }

            return;
        }

        // Sitemap index registration.
        // Match pages.xml behavior: single consolidated blog-posts.xml by default,
        // auto-paginate (blog-posts-page-N.xml) only when total posts exceed items_per_page threshold.
        $totalPosts = Post::query()->wherePublished()->count();

        if ($totalPosts > 0) {
            $latestUpdated = Post::query()
                ->wherePublished()
                ->latest('updated_at')
                ->value('updated_at');

            SiteMapManager::createPaginatedSitemaps('blog-posts', $totalPosts, $latestUpdated);
        }

        $categoryLastUpdated = Category::query()
            ->wherePublished()
            ->latest('updated_at')
            ->value('updated_at');

        if ($categoryLastUpdated) {
            SiteMapManager::addSitemap(SiteMapManager::route('blog-categories'), $categoryLastUpdated);
        }

        $tagLastUpdated = Tag::query()
            ->wherePublished()
            ->latest('updated_at')
            ->value('updated_at');

        if ($tagLastUpdated) {
            SiteMapManager::addSitemap(SiteMapManager::route('blog-tags'), $tagLastUpdated);
        }
    }
}
