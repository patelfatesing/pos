<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup the database and store in storage/app/backups';

    public function handle()
    {
        $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $storagePath = storage_path('app/backups');

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $sql = "SET FOREIGN_KEY_CHECKS=0;\n";

        foreach ($tables as $tableObj) {
            $table = array_values((array)$tableObj)[0];

            // Table structure
            $create = DB::select("SHOW CREATE TABLE `$table`")[0]->{"Create Table"};
            $sql .= "\nDROP TABLE IF EXISTS `$table`;\n$create;\n";

            // Table data
            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }, (array) $row);

                $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($storagePath . '/' . $filename, $sql);

        $this->info("Database backup saved to: $filename");
    }
}
