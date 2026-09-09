<?php

namespace Botble\Sitemap\Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SitemapContentTypeExclusionTest extends TestCase
{
    use DatabaseTransactions;
    public function test_sitemap_index_accessible_when_enabled(): void
    {
        setting()->set(['sitemap_enabled' => '1']);
        setting()->save();

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
    }

    public function test_excluded_sitemap_key_returns_404(): void
    {
        setting()->set([
            'sitemap_enabled' => '1',
            'sitemap_blog_tags_enabled' => '0',
        ]);
        setting()->save();

        $response = $this->get('/blog-tags.xml');

        $response->assertStatus(404);
    }

    public function test_enabled_sitemap_key_returns_200(): void
    {
        setting()->set([
            'sitemap_enabled' => '1',
            'sitemap_pages_enabled' => '1',
        ]);
        setting()->save();

        $response = $this->get('/pages.xml');

        $response->assertStatus(200);
    }

    public function test_excluded_paginated_sitemap_key_returns_404(): void
    {
        setting()->set([
            'sitemap_enabled' => '1',
            'sitemap_blog_posts_enabled' => '0',
        ]);
        setting()->save();

        $response = $this->get('/blog-posts-2025-04.xml');

        $response->assertStatus(404);
    }

    public function test_sitemap_settings_page_accessible_by_admin(): void
    {
        $admin = User::query()->first();

        if (! $admin instanceof User) {
            $this->markTestSkipped('No admin user found for settings page test.');
        }

        $response = $this->actingAs($admin)->get(route('sitemap.settings'));

        $response->assertStatus(200);
        $response->assertSee('Sitemap Content Types');
    }

    public function test_sitemap_settings_saves_content_type_toggles(): void
    {
        $admin = User::query()->first();

        if (! $admin instanceof User) {
            $this->markTestSkipped('No admin user found for settings save test.');
        }

        $response = $this->actingAs($admin)->put(route('sitemap.settings'), [
            'sitemap_enabled' => '1',
            'sitemap_items_per_page' => 1000,
            'sitemap_pages_enabled' => '1',
            'sitemap_blog_tags_enabled' => '0',
            'indexnow_enabled' => '0',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertEquals('0', setting('sitemap_blog_tags_enabled'));
        $this->assertEquals('1', setting('sitemap_pages_enabled'));
    }
}
