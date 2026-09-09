<?php

namespace Botble\Page\Http\Controllers;

use Botble\DataSynchronize\Http\Controllers\ImportController;
use Botble\DataSynchronize\Importer\Importer;
use Botble\Page\Importers\PageImporter;

class ImportPageController extends ImportController
{
    protected function getImporter(): Importer
    {
        return PageImporter::make();
    }
}
