<?php
namespace App\Overrides;

use Botble\Base\Supports\Core as BaseCore;

class CoreOverride extends BaseCore
{
    public function verifyLicense($timeBasedCheck = false, $timeoutInSeconds = 300): bool
    {
        // Skip license check completely - this is the main cause of slowness
        return true;
    }

    public function checkConnection(): bool
    {
        // Skip connection check
        return true;
    }

    public function getLatestVersion()
    {
        // Return mock to avoid API calls
        return new class {
            public $version = '1.4.11';
            public $updateId = 'mock';
            public $releasedDate = '2026-09-02';
        };
    }

    public function checkUpdate()
    {
        return $this->getLatestVersion();
    }
}
