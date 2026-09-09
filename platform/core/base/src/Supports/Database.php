<?php

namespace Botble\Base\Supports;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;
use Throwable;

class Database
{
    public static function restoreFromPath(string $pathToSqlFile, ?string $connection = null): void
    {
        if (! File::exists($pathToSqlFile) || File::size($pathToSqlFile) < 1024) {
            return;
        }

        static::resetSchema($connection);

        $sql = static::sanitizeSql((string) file_get_contents($pathToSqlFile));

        $errors = [];

        try {
            DB::connection($connection)->unprepared($sql);

            if (static::importSucceeded($connection)) {
                return;
            }
        } catch (Throwable $exception) {
            $errors[] = 'PDO: ' . $exception->getMessage();
        }

        static::resetSchema($connection);

        try {
            static::executeStatementsIndividually($sql, $connection);

            if (static::importSucceeded($connection)) {
                return;
            }
        } catch (Throwable $exception) {
            $errors[] = 'Statements: ' . $exception->getMessage();
        }

        static::resetSchema($connection);

        try {
            static::importViaShell($pathToSqlFile, $connection);

            if (static::importSucceeded($connection)) {
                return;
            }

            $errors[] = 'Shell: import completed but required tables are missing.';
        } catch (Throwable $exception) {
            $errors[] = 'Shell: ' . $exception->getMessage();
        }

        throw new Exception(
            'Failed to import database from ' . basename($pathToSqlFile) . '. Tried multiple methods:' . PHP_EOL
            . '- ' . implode(PHP_EOL . '- ', $errors)
        );
    }

    protected static function resetSchema(?string $connection): void
    {
        DB::purge($connection);

        $db = DB::connection($connection);
        $db->setDatabaseName($db->getDatabaseName());

        try {
            $db->getSchemaBuilder()->dropAllTables();
        } catch (Throwable) {
            // Ignore: tables may not exist on a fresh install, or a previous
            // attempt may have left the schema in a partial state.
        }
    }

    protected static function sanitizeSql(string $sql): string
    {
        $patterns = [
            '/^\s*LOCK\s+TABLES[^;]*;\s*$/mi' => '',
            '/^\s*UNLOCK\s+TABLES\s*;\s*$/mi' => '',
            '/\/\*!\d+\s*DEFINER\s*=[^*]*\*\//i' => '',
            '/^\s*DELIMITER\s+.*$/mi' => '',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $sql);
    }

    protected static function executeStatementsIndividually(string $sql, ?string $connection = null): void
    {
        $db = DB::connection($connection);

        foreach (static::splitStatements($sql) as $statement) {
            $statement = trim($statement);

            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }

            $db->unprepared($statement);
        }
    }

    protected static function splitStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($inString) {
                $buffer .= $char;

                if ($char === $stringChar && $prev !== '\\') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '\'' || $char === '"' || $char === '`') {
                $inString = true;
                $stringChar = $char;
                $buffer .= $char;

                continue;
            }

            if ($char === ';') {
                $statements[] = $buffer;
                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    protected static function importViaShell(string $pathToSqlFile, ?string $connection = null): void
    {
        $config = DB::connection($connection)->getConfig();

        $command = sprintf(
            'mysql --init-command="SET sql_mode=\'\'" --user=%s --password=%s --host=%s --port=%s %s < %s',
            escapeshellarg((string) ($config['username'] ?? '')),
            escapeshellarg((string) ($config['password'] ?? '')),
            escapeshellarg((string) ($config['host'] ?? '')),
            escapeshellarg((string) ($config['port'] ?? '')),
            escapeshellarg((string) ($config['database'] ?? '')),
            escapeshellarg($pathToSqlFile),
        );

        try {
            Process::fromShellCommandline($command)->mustRun();

            return;
        } catch (Throwable $exception) {
            if (! function_exists('system')) {
                throw new Exception('Process and system() are not available: ' . $exception->getMessage());
            }
        }

        $output = '';
        $resultCode = 0;

        ob_start();
        $result = system($command, $resultCode);
        $output = (string) ob_get_clean();

        if ($result === false || $resultCode !== 0) {
            throw new Exception('system() failed (code ' . $resultCode . '): ' . trim($output));
        }
    }

    protected static function importSucceeded(?string $connection = null): bool
    {
        try {
            DB::purge($connection);

            $schema = Schema::connection($connection);

            return $schema->hasTable('settings') || $schema->hasTable('migrations');
        } catch (Throwable) {
            return false;
        }
    }
}
