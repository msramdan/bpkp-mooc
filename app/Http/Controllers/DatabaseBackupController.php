<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:database backup view', ['only' => ['index']]),
            new Middleware('permission:database backup download', ['only' => ['download']]),
        ];
    }

    public function index(): View
    {
        return view('database-backups.index');
    }

    public function download(Request $request): StreamedResponse|RedirectResponse
    {
        try {
            $sql = $this->buildSqlBackup();
        } catch (\Throwable $e) {
            return to_route('database-backups.index')->with('error', __('Backup failed. Please contact administrator.'));
        }

        if ($sql === '') {
            return to_route('database-backups.index')->with('error', __('Backup failed. Please contact administrator.'));
        }

        $filename = 'backup-'.$request->user()->id.'-'.now()->format('Ymd-His').'.sql';

        return response()->streamDownload(function () use ($sql): void {
            echo $sql;
        }, $filename, [
            'Content-Type' => 'application/sql; charset=UTF-8',
        ]);
    }

    private function buildSqlBackup(): string
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return '';
        }

        $database = DB::connection()->getDatabaseName();
        $pdo = DB::connection()->getPdo();
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->values();

        $dump = [];
        $dump[] = '-- --------------------------------------------------------';
        $dump[] = '-- BPKP MOOC Database Backup';
        $dump[] = '-- Database: '.$database;
        $dump[] = '-- Exported at: '.now()->format('Y-m-d H:i:s');
        $dump[] = '-- --------------------------------------------------------';
        $dump[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $dump[] = '';

        foreach ($tables as $table) {
            $tableName = str_replace('`', '``', $table);
            $create = DB::selectOne('SHOW CREATE TABLE `'.$tableName.'`');
            $createSql = (array) $create;
            $createStatement = $createSql['Create Table'] ?? $createSql['Create View'] ?? null;

            if (! $createStatement) {
                continue;
            }

            $dump[] = '-- Table structure for `'.$tableName.'`';
            $dump[] = 'DROP TABLE IF EXISTS `'.$tableName.'`;';
            $dump[] = $createStatement.';';
            $dump[] = '';

            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $columns = array_keys((array) $rows->first());
            $columnSql = implode(', ', array_map(fn ($column) => '`'.str_replace('`', '``', $column).'`', $columns));
            $dump[] = '-- Dumping data for table `'.$tableName.'`';

            foreach ($rows as $row) {
                $values = [];
                foreach ((array) $row as $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                        continue;
                    }
                    $values[] = $pdo->quote((string) $value);
                }

                $dump[] = 'INSERT INTO `'.$tableName.'` ('.$columnSql.') VALUES ('.implode(', ', $values).');';
            }
            $dump[] = '';
        }

        $dump[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $dump[] = '';

        return implode("\n", $dump);
    }
}
