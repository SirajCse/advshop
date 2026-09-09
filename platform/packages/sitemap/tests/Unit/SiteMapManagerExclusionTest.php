<?php

namespace Botble\Sitemap\Tests\Unit;

use Botble\Sitemap\Sitemap;
use Botble\Theme\Supports\SiteMapManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SiteMapManagerExclusionTest extends TestCase
{
    use DatabaseTransactions;

    protected SiteMapManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure clean state: explicitly enable all content types
        $settings = [];

        foreach (SiteMapManager::getExcludableKeys() as $key) {
            $settings['sitemap_' . str_replace('-', '_', $key) . '_enabled'] = '1';
        }

        setting()->set($settings);
        setting()->save();
        setting()->forgetAll();

        $this->manager = new SiteMapManager(app(Sitemap::class));
    }

    public function test_get_excludable_keys_returns_expected_keys(): void
    {
        $keys = SiteMapManager::getExcludableKeys();

        $this->assertContains('pages', $keys);
        $this->assertContains('blog-posts', $keys);
        $this->assertContains('blog-categories', $keys);
        $this->assertContains('blog-tags', $keys);
        $this->assertContains('galleries', $keys);
    }

    public function test_key_is_not_excluded_by_default(): void
    {
        $this->assertFalse($this->manager->isKeyExcluded('pages'));
        $this->assertFalse($this->manager->isKeyExcluded('blog-posts'));
        $this->assertFalse($this->manager->isKeyExcluded('blog-categories'));
        $this->assertFalse($this->manager->isKeyExcluded('blog-tags'));
        $this->assertFalse($this->manager->isKeyExcluded('galleries'));
    }

    public function test_key_is_excluded_when_setting_is_disabled(): void
    {
        setting()->set(['sitemap_blog_tags_enabled' => '0']);
        setting()->save();

        $this->assertTrue($this->manager->isKeyExcluded('blog-tags'));
    }

    public function test_key_is_not_excluded_when_setting_is_enabled(): void
    {
        setting()->set(['sitemap_blog_tags_enabled' => '1']);
        setting()->save();

        $this->assertFalse($this->manager->isKeyExcluded('blog-tags'));
    }

    public function test_monthly_archive_key_inherits_parent_exclusion(): void
    {
        setting()->set(['sitemap_blog_posts_enabled' => '0']);
        setting()->save();

        $this->assertTrue($this->manager->isKeyExcluded('blog-posts'));
        $this->assertTrue($this->manager->isKeyExcluded('blog-posts-2025-04'));
        $this->assertTrue($this->manager->isKeyExcluded('blog-posts-2025-04-page-2'));
    }

    public function test_monthly_archive_key_not_excluded_when_parent_enabled(): void
    {
        setting()->set(['sitemap_blog_posts_enabled' => '1']);
        setting()->save();

        $this->assertFalse($this->manager->isKeyExcluded('blog-posts'));
        $this->assertFalse($this->manager->isKeyExcluded('blog-posts-2025-04'));
        $this->assertFalse($this->manager->isKeyExcluded('blog-posts-2025-04-page-2'));
    }

    public function test_excluding_blog_posts_does_not_exclude_blog_categories(): void
    {
        setting()->set([
            'sitemap_blog_posts_enabled' => '0',
            'sitemap_blog_categories_enabled' => '1',
        ]);
        setting()->save();

        $this->assertTrue($this->manager->isKeyExcluded('blog-posts'));
        $this->assertFalse($this->manager->isKeyExcluded('blog-categories'));
    }

    public function test_unknown_key_is_never_excluded(): void
    {
        $this->assertFalse($this->manager->isKeyExcluded('products'));
        $this->assertFalse($this->manager->isKeyExcluded('custom-content'));
    }

    public function test_removed_keys_are_filtered_from_get_keys(): void
    {
        $this->manager->registerKey('blog-tags');
        $this->manager->removeKey('blog-tags');

        $keys = $this->manager->getKeys();

        $this->assertNotContains('blog-tags', $keys);
    }

    public function test_keys_regex_matches_consolidated_blog_posts_pagination(): void
    {
        $this->manager->registerKey('blog-posts');

        $regex = '/' . $this->manager->getKeysRegex() . '/';

        $this->assertSame(1, preg_match($regex, 'blog-posts'));
        $this->assertSame(1, preg_match($regex, 'blog-posts-page-1'));
        $this->assertSame(1, preg_match($regex, 'blog-posts-page-42'));
        $this->assertSame(1, preg_match($regex, 'blog-posts-2025-04'));
        $this->assertSame(1, preg_match($regex, 'blog-posts-2025-04-page-2'));
    }

    public function test_keys_regex_supports_pagination_for_arbitrary_registered_keys(): void
    {
        $this->manager->registerKey(['products', 'properties', 'projects', 'jobs', 'job-categories']);

        $regex = '/' . $this->manager->getKeysRegex() . '/';

        foreach (['products', 'properties', 'projects', 'jobs', 'job-categories'] as $key) {
            $this->assertSame(1, preg_match($regex, $key), "base key {$key}");
            $this->assertSame(1, preg_match($regex, "{$key}-page-3"), "page pattern for {$key}");
            $this->assertSame(1, preg_match($regex, "{$key}-2026-04"), "legacy archive for {$key}");
            $this->assertSame(1, preg_match($regex, "{$key}-2026-04-page-2"), "legacy archive paginated for {$key}");
        }
    }

    public function test_multiple_keys_can_be_excluded_simultaneously(): void
    {
        setting()->set([
            'sitemap_blog_tags_enabled' => '0',
            'sitemap_galleries_enabled' => '0',
            'sitemap_pages_enabled' => '1',
        ]);
        setting()->save();

        $this->assertTrue($this->manager->isKeyExcluded('blog-tags'));
        $this->assertTrue($this->manager->isKeyExcluded('galleries'));
        $this->assertFalse($this->manager->isKeyExcluded('pages'));
    }
}
