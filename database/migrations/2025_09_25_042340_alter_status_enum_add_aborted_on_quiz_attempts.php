<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Tambahkan 'aborted' ke daftar enum
        DB::statement("
            ALTER TABLE quiz_attempts
            MODIFY COLUMN status ENUM('started','submitted','aborted')
            NOT NULL DEFAULT 'started'
        ");
    }

    public function down(): void
    {
        // Revert ke enum lama (tanpa 'aborted')
        DB::statement("
            ALTER TABLE quiz_attempts
            MODIFY COLUMN status ENUM('started','submitted')
            NOT NULL DEFAULT 'started'
        ");
    }
};
