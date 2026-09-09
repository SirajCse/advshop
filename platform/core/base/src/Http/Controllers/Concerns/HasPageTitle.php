<?php

namespace Botble\Base\Http\Controllers\Concerns;

use Botble\Base\Facades\PageTitle;

trait HasPageTitle
{
    protected function pageTitle(string $title, bool $registerBreadcrumb = true): void
    {
        PageTitle::setTitle($title);

        if ($registerBreadcrumb) {
            $this->breadcrumb()->add($title);
        }
    }

    protected function getPageTitle($withSiteName = true): string
    {
        return PageTitle::getTitle($withSiteName);
    }
}
