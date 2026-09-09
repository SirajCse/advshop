<?php

namespace Botble\Installer\Services;

use Botble\Base\Services\ClearCacheService;
use Botble\Base\Supports\Database;
use Illuminate\Validation\ValidationException;
use Throwable;

class ImportDatabaseService
{
    public function handle(string $path): void
    {
        try {
            Database::restoreFromPath($path);

            ClearCacheService::make()->purgeAll();
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'database' => [$exception->getMessage()],
            ]);
        }
    }
}
