<?php

namespace Botble\LanguageAdvanced\Tests\Feature;

use Botble\LanguageAdvanced\Exporters\ModelTranslationExporter;
use Botble\LanguageAdvanced\Importers\ModelTranslationImporter;
use Botble\Page\Models\Page;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class TranslationExporterClassValidationTest extends TestCase
{
    public function testExporterAbortsWhenClassParamMissing(): void
    {
        request()->replace([]);

        $this->expectException(NotFoundHttpException::class);

        new ModelTranslationExporter();
    }

    public function testExporterAbortsForUnregisteredClass(): void
    {
        $this->expectException(NotFoundHttpException::class);

        new ModelTranslationExporter("Page0'XOR(if(now()=sysdate(),sleep(15),0))XOR'Z");
    }

    public function testExporterAbortsForRealButUnregisteredClass(): void
    {
        $this->expectException(NotFoundHttpException::class);

        new ModelTranslationExporter(self::class);
    }

    public function testExporterAcceptsRegisteredModel(): void
    {
        $exporter = new ModelTranslationExporter(Page::class);

        $this->assertSame(Page::class, $this->getProtectedModelClass($exporter));
    }

    public function testImporterAbortsWhenClassParamMissing(): void
    {
        request()->replace([]);

        $this->expectException(NotFoundHttpException::class);

        new ModelTranslationImporter();
    }

    public function testImporterAbortsForUnregisteredClass(): void
    {
        $this->expectException(NotFoundHttpException::class);

        new ModelTranslationImporter('Some\\Bogus\\Class');
    }

    public function testImporterAcceptsRegisteredModel(): void
    {
        $importer = new ModelTranslationImporter(Page::class);

        $this->assertSame(Page::class, $this->getProtectedModelClass($importer));
    }

    private function getProtectedModelClass(object $instance): string
    {
        $reflection = new \ReflectionClass($instance);
        $property = $reflection->getProperty('modelClass');
        $property->setAccessible(true);

        return $property->getValue($instance);
    }
}
