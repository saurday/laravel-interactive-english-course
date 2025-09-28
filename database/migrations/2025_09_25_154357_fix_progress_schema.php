<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ===== 1) Jika tabel belum ada, CREATE langsung dengan skema final =====
        if (!Schema::hasTable('progress')) {
            Schema::create('progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('course_resource_id')->constrained('course_resources')->cascadeOnDelete();
                $table->boolean('completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id','course_resource_id'], 'progress_user_resource_unique');
            });
            return; // selesai
        }

        // ===== 2) Jika tabel SUDAH ada, ALTER seperlunya =====
        Schema::table('progress', function (Blueprint $table) {
            if (!Schema::hasColumn('progress','user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')
                    ->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('progress','course_resource_id')) {
                $table->foreignId('course_resource_id')->nullable()->after('user_id')
                    ->constrained('course_resources')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('progress','completed')) {
                $table->boolean('completed')->default(false)->after('course_resource_id');
            }
            if (!Schema::hasColumn('progress','completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('completed');
            }
        });

        // Backfill user_id dari mahasiswa_id (kalau ada)
        if (Schema::hasColumn('progress','mahasiswa_id')) {
            DB::statement('UPDATE progress SET user_id = mahasiswa_id WHERE user_id IS NULL');
        }

        // Bersihkan kolom lama
        Schema::table('progress', function (Blueprint $table) {
            if (Schema::hasColumn('progress','mahasiswa_id')) {
                try { $table->dropForeign(['mahasiswa_id']); } catch (\Throwable $e) {}
                $table->dropColumn('mahasiswa_id');
            }
            if (Schema::hasColumn('progress','week'))       $table->dropColumn('week');
            if (Schema::hasColumn('progress','percentage')) $table->dropColumn('percentage');

            // Unique index (buat kalau belum ada)
            try { $table->unique(['user_id','course_resource_id'], 'progress_user_resource_unique'); } catch (\Throwable $e) {}
        });

        // (Opsional) jadikan NOT NULL di MySQL
        try { DB::statement('ALTER TABLE progress MODIFY user_id BIGINT UNSIGNED NOT NULL'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE progress MODIFY course_resource_id BIGINT UNSIGNED NOT NULL'); } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // rollback simpel: drop table (aman untuk dev)
        Schema::dropIfExists('progress');
    }
};
