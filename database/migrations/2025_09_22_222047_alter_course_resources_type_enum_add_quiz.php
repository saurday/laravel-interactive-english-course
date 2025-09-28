<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // tambahkan 'quiz' ke daftar ENUM
        DB::statement("
            ALTER TABLE course_resources
            MODIFY COLUMN type ENUM('text','video','file','quiz') NOT NULL
        ");
    }

    public function down(): void
    {
        // rollback ke enum lama (tanpa 'quiz')
        DB::statement("
            ALTER TABLE course_resources
            MODIFY COLUMN type ENUM('text','video','file') NOT NULL
        ");
    }
};
