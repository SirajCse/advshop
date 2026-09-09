<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\DataSynchronize\Http\Controllers\ImportController;
use Botble\DataSynchronize\Importer\Importer;
use Botble\Ecommerce\Importers\ProductSpecificationImporter;

class ImportProductSpecificationController extends ImportController
{
    protected function getImporter(): Importer
    {
        return ProductSpecificationImporter::make();
    }
}
