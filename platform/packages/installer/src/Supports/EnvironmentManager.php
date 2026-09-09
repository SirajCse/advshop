<?php

namespace Botble\Installer\Supports;

use Illuminate\Http\Request;
use Throwable;

class EnvironmentManager
{
    public function save(Request $request): string
    {
        $results = trans('packages/installer::installer.environment.success');

        $content = file_get_contents(base_path('.env.example'));

        $replacements = [
            'APP_NAME' => [
                'default' => '"Your App"',
                'value' => '"' . str_replace('"', '', $request->input('app_name')) . '"',
            ],
            'APP_URL' => [
                'default' => 'http:\/\/localhost',
                'value' => $request->input('app_url'),
            ],
            'FORCE_ROOT_URL' => [
                'default' => 'https:\/\/your-domain.com',
                'value' => $request->input('app_url'),
            ],
            'DB_CONNECTION' => [
                'default' => 'mysql',
                'value' => $request->input('database_connection'),
            ],
            'DB_HOST' => [
                'default' => '127.0.0.1',
                'value' => $request->input('database_hostname'),
            ],
            'DB_PORT' => [
                'default' => '3306',
                'value' => $request->input('database_port'),
            ],
            'DB_DATABASE' => [
                'default' => '"laravel"',
                'value' => '"' . str_replace('"', '', $request->input('database_name')) . '"',
            ],
            'DB_USERNAME' => [
                'default' => '"root"',
                'value' => '"' . str_replace('"', '', $request->input('database_username')) . '"',
            ],
            'DB_PASSWORD' => [
                'default' => '"your_db_password"',
                'value' => '"' . str_replace('"', '', $request->input('database_password')) . '"',
            ],
        ];

        foreach ($replacements as $key => $replacement) {
            // Allow an optional leading "#" (and spaces/tabs, not newlines) so commented-out
            // defaults (e.g. #FORCE_ROOT_URL=...) are uncommented and written. Without this,
            // FORCE_ROOT_URL is never set, and sub-folder installs fall back to the wrong root
            // URL (e.g. http://localhost). [ \t]* avoids matching across line breaks.
            $content = preg_replace(
                '/^#?[ \t]*' . $key . '=' . $replacement['default'] . '/m',
                $key . '=' . $replacement['value'],
                $content
            );
        }

        try {
            file_put_contents(base_path('.env'), $content);
        } catch (Throwable) {
            $results = trans('packages/installer::installer.environment.errors');
        }

        return $results;
    }

    public function turnOffDebugMode(): void
    {
        $content = file_get_contents(base_path('.env'));

        $content = preg_replace('/^APP_DEBUG=true/m', 'APP_DEBUG=false', $content);

        try {
            file_put_contents(base_path('.env'), $content);
        } catch (Throwable) {
        }
    }
}
