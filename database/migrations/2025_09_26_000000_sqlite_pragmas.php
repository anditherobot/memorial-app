<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
            try {
                DB::statement('PRAGMA journal_mode = WAL;');
            } catch (\Throwable $e) {
                // Some environments may not allow changing journal mode; ignore.
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            try {
                DB::statement('PRAGMA journal_mode = DELETE;');
            } catch (\Throwable $e) {
                // ignore
            }
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }
};

