<?php

namespace Botble\Base\Tests\Unit;

use Botble\Base\Supports\Core;
use Exception;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;
use ZipArchive;

class ValidateUpdateFileTest extends TestCase
{
    private Core $core;

    private ReflectionMethod $validateMethod;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = Core::make();
        $this->validateMethod = new ReflectionMethod($this->core, 'validateUpdateFile');
        $this->validateMethod->setAccessible(true);

        $this->tempDir = sys_get_temp_dir() . '/botble_update_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        // Clean up any remaining test files
        array_map('unlink', glob($this->tempDir . '/*') ?: []);
        @rmdir($this->tempDir);

        parent::tearDown();
    }

    public function test_rejects_empty_file(): void
    {
        $filePath = $this->tempDir . '/empty.zip';
        file_put_contents($filePath, '');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('too small (0 bytes)');

        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_rejects_file_smaller_than_1kb(): void
    {
        $filePath = $this->tempDir . '/small.zip';
        file_put_contents($filePath, str_repeat('x', 512));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('too small (512 bytes)');

        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_deletes_file_when_too_small(): void
    {
        $filePath = $this->tempDir . '/small.zip';
        file_put_contents($filePath, 'tiny');

        try {
            $this->validateMethod->invoke($this->core, $filePath);
        } catch (Exception) {
        }

        $this->assertFileDoesNotExist($filePath);
    }

    public function test_rejects_non_zip_file(): void
    {
        $filePath = $this->tempDir . '/not_a_zip.zip';
        file_put_contents($filePath, str_repeat('this is not a zip file', 100));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not a valid zip archive');

        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_deletes_file_when_not_valid_zip(): void
    {
        $filePath = $this->tempDir . '/not_a_zip.zip';
        file_put_contents($filePath, str_repeat('garbage', 200));

        try {
            $this->validateMethod->invoke($this->core, $filePath);
        } catch (Exception) {
        }

        $this->assertFileDoesNotExist($filePath);
    }

    public function test_rejects_zip_containing_env_file(): void
    {
        $filePath = $this->createZipWith(['.env' => 'APP_KEY=secret']);

        $this->expectException(ValidationException::class);

        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_rejects_zip_without_core_json(): void
    {
        $filePath = $this->createZipWith(['some_file.txt' => 'hello']);

        $this->expectException(ValidationException::class);

        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_rejects_zip_with_invalid_core_json(): void
    {
        $filePath = $this->createZipWith([
            'platform/core/core.json' => 'not valid json',
        ]);

        $this->expectException(ValidationException::class);

        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_rejects_zip_with_mismatched_product_id(): void
    {
        $filePath = $this->createZipWith([
            'platform/core/core.json' => json_encode([
                'productId' => 'WRONG_PRODUCT_ID',
                'source' => 'envato',
                'apiUrl' => 'https://license.botble.com',
                'apiKey' => 'test',
                'version' => '99.0.0',
                'marketplaceUrl' => 'https://marketplace.botble.com',
                'marketplaceToken' => 'test',
            ]),
        ]);

        $this->expectException(ValidationException::class);

        try {
            $this->validateMethod->invoke($this->core, $filePath);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('productId', $e->errors());
            $this->assertStringContainsString('does not match', $e->errors()['productId'][0]);

            throw $e;
        }
    }

    public function test_rejects_zip_with_lower_version(): void
    {
        $filePath = $this->createZipWithMatchingProductId('0.0.1');

        $this->expectException(ValidationException::class);

        try {
            $this->validateMethod->invoke($this->core, $filePath);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('version', $e->errors());
            $this->assertStringContainsString('lower than', $e->errors()['version'][0]);

            throw $e;
        }
    }

    public function test_rejects_zip_requiring_higher_php_version(): void
    {
        $filePath = $this->createZipWithMatchingProductId('99.0.0', '99.0.0');

        $this->expectException(ValidationException::class);

        try {
            $this->validateMethod->invoke($this->core, $filePath);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('minimumPhpVersion', $e->errors());
            $this->assertStringContainsString('v99.0.0', $e->errors()['minimumPhpVersion'][0]);
            $this->assertStringContainsString(phpversion(), $e->errors()['minimumPhpVersion'][0]);

            throw $e;
        }
    }

    public function test_accepts_valid_zip(): void
    {
        $filePath = $this->createZipWithMatchingProductId(
            $this->core->version(),
            phpversion()
        );

        $this->expectNotToPerformAssertions();

        // Should not throw
        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_accepts_zip_without_minimum_php_version(): void
    {
        $filePath = $this->createZipWithMatchingProductId(
            $this->core->version(),
            null
        );

        $this->expectNotToPerformAssertions();

        // Should not throw
        $this->validateMethod->invoke($this->core, $filePath);
    }

    public function test_zip_is_closed_after_validation_exception(): void
    {
        $filePath = $this->createZipWithMatchingProductId('0.0.1');

        try {
            $this->validateMethod->invoke($this->core, $filePath);
        } catch (ValidationException) {
        }

        // If zip was properly closed, we should be able to delete the file
        $this->assertTrue(@unlink($filePath) || ! file_exists($filePath));
    }

    /**
     * Create a zip with the given file entries.
     * A padding file is added to ensure the zip exceeds the 1KB minimum size check.
     *
     * @param array<string, string> $files filename => content
     */
    private function createZipWith(array $files): string
    {
        $filePath = $this->tempDir . '/test_' . uniqid() . '.zip';

        $zip = new ZipArchive();
        $zip->open($filePath, ZipArchive::CREATE);

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }

        // Pad with incompressible random data to exceed the 1KB minimum size check
        $zip->addFromString('_padding.bin', random_bytes(2048));

        $zip->close();

        return $filePath;
    }

    /**
     * Create a valid zip with the real product ID from the running instance.
     */
    private function createZipWithMatchingProductId(
        string $version,
        ?string $minimumPhpVersion = '8.0.0',
    ): string {
        $coreData = $this->core->getCoreFileData();

        $content = [
            'productId' => $coreData['productId'],
            'source' => $coreData['source'] ?? 'envato',
            'apiUrl' => $coreData['apiUrl'] ?? 'https://license.botble.com',
            'apiKey' => $coreData['apiKey'] ?? 'test',
            'version' => $version,
            'marketplaceUrl' => $coreData['marketplaceUrl'] ?? 'https://marketplace.botble.com',
            'marketplaceToken' => $coreData['marketplaceToken'] ?? 'test',
        ];

        if ($minimumPhpVersion !== null) {
            $content['minimumPhpVersion'] = $minimumPhpVersion;
        }

        return $this->createZipWith([
            'platform/core/core.json' => json_encode($content),
        ]);
    }
}
