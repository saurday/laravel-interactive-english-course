<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_resources', function (Blueprint $table) {
            if (!Schema::hasColumn('course_resources', 'assignment_id')) {
                $table->foreignId('assignment_id')
                      ->nullable()
                      ->after('quiz_id')
                      ->constrained('assignments')
                      ->nullOnDelete();
            }

            // Kalau kolom 'type' kamu ENUM dan belum ada 'assignment',
            // kamu bisa ubah via raw SQL (MySQL). Kalau 'type' string, lewati bagian ini.
            // try {
            //     DB::statement("ALTER TABLE course_resources
            //         MODIFY COLUMN type ENUM('text','video','file','quiz','assignment','composite') NOT NULL");
            // } catch (\Throwable $e) { /* ignore jika bukan ENUM */ }
        });
    }

    public function down(): void
    {
        Schema::table('course_resources', function (Blueprint $table) {
            if (Schema::hasColumn('course_resources', 'assignment_id')) {
                $table->dropConstrainedForeignId('assignment_id');
            }
        });
    }
};
