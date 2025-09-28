<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Drop FK di tabel lama, kalau ada
        if (Schema::hasTable('progress')) {
            Schema::table('progress', function (Blueprint $table) {
                // Hanya drop kalau kolomnya memang ada
                if (Schema::hasColumn('progress', 'mahasiswa_id')) {
                    // coba drop berdasarkan nama index default Laravel
                    try {
                        $table->dropForeign(['mahasiswa_id']);
                    } catch (\Throwable $e) {
                        // Abaikan jika sudah ter-drop / nama berbeda
                    }
                }
            });

            // 2) Rename 'progress' -> 'progresses'
            Schema::rename('progress', 'progresses');
        }

        // 3) Tambah kolom baru + buang kolom lama di tabel baru
        if (Schema::hasTable('progresses')) {
            Schema::table('progresses', function (Blueprint $table) {
                if (!Schema::hasColumn('progresses', 'user_id')) {
                    $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('progresses', 'course_resource_id')) {
                    $table->foreignId('course_resource_id')->after('user_id')->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('progresses', 'completed')) {
                    $table->boolean('completed')->default(false)->after('course_resource_id');
                }
                if (!Schema::hasColumn('progresses', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('completed');
                }

                // drop kolom lama jika masih ada
                if (Schema::hasColumn('progresses', 'mahasiswa_id')) {
                    try {
                        $table->dropColumn('mahasiswa_id');
                    } catch (\Throwable $e) {}
                }
                if (Schema::hasColumn('progresses', 'week')) {
                    try {
                        $table->dropColumn('week');
                    } catch (\Throwable $e) {}
                }
                if (Schema::hasColumn('progresses', 'percentage')) {
                    try {
                        $table->dropColumn('percentage');
                    } catch (\Throwable $e) {}
                }

                // unique per user-resource (beri nama agar jelas)
                try {
                    $table->unique(['user_id', 'course_resource_id'], 'progresses_uid_res_unique');
                } catch (\Throwable $e) {
                    // abaikan jika sudah ada
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('progresses')) {
            Schema::table('progresses', function (Blueprint $table) {
                // rollback minimal
                try { $table->dropUnique('progresses_uid_res_unique'); } catch (\Throwable $e) {}
                if (Schema::hasColumn('progresses', 'user_id')) {
                    try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
                }
                if (Schema::hasColumn('progresses', 'course_resource_id')) {
                    try { $table->dropForeign(['course_resource_id']); } catch (\Throwable $e) {}
                }
                try { $table->dropColumn(['user_id','course_resource_id','completed','completed_at']); } catch (\Throwable $e) {}

                // kembalikan kolom lama (opsional)
                if (!Schema::hasColumn('progresses', 'mahasiswa_id')) {
                    $table->unsignedBigInteger('mahasiswa_id')->nullable();
                }
                if (!Schema::hasColumn('progresses', 'week')) {
                    $table->unsignedInteger('week')->nullable();
                }
                if (!Schema::hasColumn('progresses', 'percentage')) {
                    $table->unsignedInteger('percentage')->default(0);
                }
            });

            // rename balik
            Schema::rename('progresses', 'progress');
        }
    }
};
