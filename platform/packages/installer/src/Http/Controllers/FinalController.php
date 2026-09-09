<?php

namespace Botble\Installer\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Installer\Events\InstallerFinished;
use Botble\Installer\Services\CleanupSystemAfterInstalledService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Throwable;
class FinalController
{
    public function index(
        Request $request,
        CleanupSystemAfterInstalledService $cleanupSystemAfterInstalledService
    ): View|RedirectResponse {
        // Remove or comment out the signature check if you want to skip it
        // Or keep it for security but make sure your routes are configured properly
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('installers.requirements.index');
        }

        // Remove the installing file as installation is complete
        File::delete(storage_path('installing'));

        // Clean up unnecessary files (optional but recommended)
        try {
            $files = collect(File::files(base_path()))
                ->filter(function ($file) {
                    $fileName = $file->getFilename();

                    // Files to delete after installation
                    $filesToDelete = [
                        'database.sql',
                        'readme.txt',
                        'document.zip',
                        'docker-compose.yml'
                    ];

                    if (in_array($fileName, $filesToDelete)) {
                        return true;
                    }

                    // Delete any .sql files starting with 'database'
                    return str_starts_with($fileName, 'database') && $file->getExtension() === 'sql';
                })
                ->map(function ($file) {
                    return $file->getFilename();
                })
                ->all();

            if (! empty($files)) {
                foreach ($files as $file) {
                    File::delete(base_path($file));
                }
            }

        } catch (Throwable) {
            // Silently fail if cleanup has issues
            // Log the error if needed: Log::error('Cleanup failed: ' . $e->getMessage());
        }

        // Mark the application as installed
        BaseHelper::saveFileData(storage_path('installed'), Carbon::now()->toDateTimeString());

        // Run any post-installation cleanup tasks
        $cleanupSystemAfterInstalledService->handle();

        // Fire the installer finished event
        event(new InstallerFinished());

        // Return the final view
        return view('packages/installer::final');
    }
}
