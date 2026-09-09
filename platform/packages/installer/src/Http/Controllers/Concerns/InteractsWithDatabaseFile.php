<?php

namespace Botble\Installer\Http\Controllers\Concerns;

use Botble\Installer\Services\ImportDatabaseService;
use Illuminate\Support\Facades\File;

trait InteractsWithDatabaseFile
{
    protected function handleImportDatabaseFile(
        ImportDatabaseService $importDatabaseService,
        string $fileName,
        ?string $explicitDatabaseFile = null
    ): void {
        try {
            if ($explicitDatabaseFile) {
                $candidate = base_path($explicitDatabaseFile);

                if (File::exists($candidate)) {
                    $importDatabaseService->handle($candidate);
                    return;
                }
            }

            $databaseToImport = $this->findDatabaseFile($fileName);

            if ($databaseToImport && File::exists($databaseToImport)) {
                $importDatabaseService->handle($databaseToImport);
            } else {
                // Log error but don't throw - allow installation to continue
                Log::warning('No database file found to import for: ' . $fileName);
            }
        } catch (Throwable $e) {
            Log::error('Database import failed: ' . $e->getMessage());
            // Re-throw if you want to stop installation on database import failure
            // throw $e;
        }
    }

    protected function findDatabaseFile(string $fileName): ?string
    {
        // Priority 1: Theme-specific database file
        $candidates = [
            base_path(sprintf('database-%s.sql', $fileName)),
            database_path(sprintf('sample/database-%s.sql', $fileName)),
            database_path('sample/database.sql'),
            base_path('database.sql'),
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate) && File::size($candidate) > 1024) {
                return $candidate;
            }
        }

        return null;
    }

    protected function hasDatabaseFile(string $fileName): bool
    {
        return $this->findDatabaseFile($fileName) !== null;
    }

    protected function getDatabaseFileSize(string $fileName): int
    {
        $file = $this->findDatabaseFile($fileName);
        return $file ? File::size($file) : 0;
    }
}
