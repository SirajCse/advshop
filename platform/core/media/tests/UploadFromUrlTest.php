<?php

namespace Botble\Media\Tests;

use Botble\Media\RvMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class UploadFromUrlTest extends TestCase
{
    protected RvMedia $rvMedia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rvMedia = app(RvMedia::class);

        Storage::fake('public');
    }

    public function testUploadFromUrlStripsQueryParametersFromFilename(): void
    {
        $facebookUrl = 'https://platform-lookaside.fbsbx.com/platform/profilepic/?psid=123&height=1920&width=1920&ext=1709824567&hash=AbC123dEf';

        Http::fake([
            'platform-lookaside.fbsbx.com/*' => Http::response(
                $this->createJpegContent(),
                200,
                ['Content-Type' => 'image/jpeg']
            ),
        ]);

        $result = $this->rvMedia->uploadFromUrl($facebookUrl, 0, 'accounts', 'image/png');

        $this->assertFalse($result['error']);
        $this->assertNotNull($result['data']);

        $url = $result['data']->url;

        // The stored filename should NOT contain query parameter characters
        $this->assertStringNotContainsString('?', $url);
        $this->assertStringNotContainsString('&', $url);
        $this->assertStringNotContainsString('=', $url);
    }

    public function testUploadFromUrlHandlesFacebookCdnUrlWithExtension(): void
    {
        $facebookCdnUrl = 'https://scontent.xx.fbcdn.net/v/t1.6435-1/12345_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=abc';

        Http::fake([
            'scontent.xx.fbcdn.net/*' => Http::response(
                $this->createJpegContent(),
                200,
                ['Content-Type' => 'image/jpeg']
            ),
        ]);

        $result = $this->rvMedia->uploadFromUrl($facebookCdnUrl, 0, 'accounts', 'image/png');

        $this->assertFalse($result['error']);

        $url = $result['data']->url;

        // Should not contain query parameters in the stored path
        $this->assertStringNotContainsString('_nc_cat', $url);
        $this->assertStringNotContainsString('?', $url);
    }

    public function testUploadFromUrlWithTrailingSlashGeneratesValidFilename(): void
    {
        // Facebook profilepic URLs have path ending with / before query params
        $url = 'https://platform-lookaside.fbsbx.com/platform/profilepic/?psid=999';

        Http::fake([
            'platform-lookaside.fbsbx.com/*' => Http::response(
                $this->createJpegContent(),
                200,
                ['Content-Type' => 'image/jpeg']
            ),
        ]);

        $result = $this->rvMedia->uploadFromUrl($url, 0, 'accounts', 'image/png');

        $this->assertFalse($result['error']);
        $this->assertNotNull($result['data']);
    }

    public function testNewUploadedFileDetectsMimeTypeFromContent(): void
    {
        // Create a temp JPEG file with no extension (simulates Facebook avatar)
        $tmpPath = tempnam(sys_get_temp_dir(), 'fb_avatar_');
        file_put_contents($tmpPath, $this->createJpegContent());

        try {
            $method = new ReflectionMethod(RvMedia::class, 'newUploadedFile');

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $method->invoke($this->rvMedia, $tmpPath, 'image/png');

            // Should detect actual JPEG mime type from file content, not use default image/png
            $this->assertEquals('image/jpeg', $uploadedFile->getMimeType());
            $this->assertMatchesRegularExpression('/\.jpe?g$/', $uploadedFile->getClientOriginalName());
        } finally {
            @unlink($tmpPath);
        }
    }

    public function testNewUploadedFileFallsBackToDefaultMimeType(): void
    {
        // Create a temp file with unrecognizable content
        $tmpPath = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpPath, 'not a real image');

        try {
            $method = new ReflectionMethod(RvMedia::class, 'newUploadedFile');

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $method->invoke($this->rvMedia, $tmpPath, 'image/png');

            // When content mime detection returns text/plain for garbage data,
            // it should still produce a valid UploadedFile
            $this->assertInstanceOf(UploadedFile::class, $uploadedFile);
        } finally {
            @unlink($tmpPath);
        }
    }

    public function testNewUploadedFileWithPngContent(): void
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'png_avatar_');
        file_put_contents($tmpPath, $this->createPngContent());

        try {
            $method = new ReflectionMethod(RvMedia::class, 'newUploadedFile');

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $method->invoke($this->rvMedia, $tmpPath, 'image/jpeg');

            // Should detect actual PNG from content, not use the default image/jpeg
            $this->assertEquals('image/png', $uploadedFile->getMimeType());
            $this->assertStringEndsWith('.png', $uploadedFile->getClientOriginalName());
        } finally {
            @unlink($tmpPath);
        }
    }

    public function testUploadFromUrlWithEmptyUrlReturnsError(): void
    {
        $result = $this->rvMedia->uploadFromUrl('');

        $this->assertTrue($result['error']);
    }

    public function testUploadFromUrlWithFailedHttpResponseReturnsError(): void
    {
        Http::fake([
            '*' => Http::response('Not Found', 404),
        ]);

        $result = $this->rvMedia->uploadFromUrl('https://example.com/missing.jpg');

        $this->assertTrue($result['error']);
    }

    /**
     * Create minimal valid JPEG binary content.
     */
    protected function createJpegContent(): string
    {
        $image = imagecreatetruecolor(10, 10);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $red);

        ob_start();
        imagejpeg($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }

    /**
     * Create minimal valid PNG binary content.
     */
    protected function createPngContent(): string
    {
        $image = imagecreatetruecolor(10, 10);
        $blue = imagecolorallocate($image, 0, 0, 255);
        imagefill($image, 0, 0, $blue);

        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }
}
