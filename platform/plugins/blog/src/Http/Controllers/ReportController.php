<?php

namespace Botble\Blog\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Models\Tag;

class ReportController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/blog::base.menu_name'))
            ->add(trans('plugins/blog::reports.name'), route('blog.reports.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('plugins/blog::reports.name'));

        $totalPosts = Post::query()->count();
        $totalViews = Post::query()->sum('views');
        $totalCategories = Category::query()->count();
        $totalTags = Tag::query()->count();

        $publishedPosts = Post::query()->wherePublished()->count();
        $draftPosts = Post::query()->where('status', 'draft')->count();
        $pendingPosts = Post::query()->where('status', 'pending')->count();

        $topViewedPosts = Post::query()
            ->with(['slugable', 'categories'])
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $recentPosts = Post::query()
            ->with(['slugable', 'categories'])
            ->latest()
            ->limit(10)
            ->get();

        $postsPerCategory = Category::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(10)
            ->get();

        return view('plugins/blog::reports.index', compact(
            'totalPosts',
            'totalViews',
            'totalCategories',
            'totalTags',
            'publishedPosts',
            'draftPosts',
            'pendingPosts',
            'topViewedPosts',
            'recentPosts',
            'postsPerCategory'
        ));
    }
}
