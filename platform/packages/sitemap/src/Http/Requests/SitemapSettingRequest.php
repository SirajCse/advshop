<?php

namespace Botble\Sitemap\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class SitemapSettingRequest extends Request
{
    public function rules(): array
    {
        return apply_filters('sitemap_settings_validation_rules', [
            'sitemap_enabled' => [new OnOffRule()],
            'sitemap_items_per_page' => ['nullable', 'integer', 'min:10', 'max:100000'],
            'sitemap_pages_enabled' => [new OnOffRule()],
            'indexnow_enabled' => [new OnOffRule()],
            'indexnow_api_key' => ['nullable', 'string', 'uuid', 'max:255'],
        ]);
    }
}
