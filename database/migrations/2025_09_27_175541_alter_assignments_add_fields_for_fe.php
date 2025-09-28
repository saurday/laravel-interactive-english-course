<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Tambah kolom-kolom baru jika belum ada
            if (!Schema::hasColumn('assignments', 'kelas_id')) {
                $table->foreignId('kelas_id')
                      ->nullable() // sementara nullable biar aman migrasi
                      ->after('id')
                      ->constrained('kelas')   // sesuaikan nama tabelnya: 'kelas'
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('assignments', 'instructions')) {
                $table->text('instructions')->nullable()->after('title');
            }

            if (!Schema::hasColumn('assignments', 'due_date')) {
                $table->dateTime('due_date')->nullable()->after('instructions');
            }

            if (!Schema::hasColumn('assignments', 'max_score')) {
                $table->unsignedSmallInteger('max_score')->default(100)->after('due_date');
            }

            if (!Schema::hasColumn('assignments', 'allow_file')) {
                $table->boolean('allow_file')->default(true)->after('max_score');
            }

            if (!Schema::hasColumn('assignments', 'created_by')) {
                $table->foreignId('created_by')
                      ->nullable()
                      ->after('allow_file')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });

        // (Opsional) index bantu
        if (!Schema::hasColumn('assignments', 'kelas_id')) return;
        Schema::table('assignments', function (Blueprint $table) {
            $table->index(['kelas_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Hapus kolom yang kita tambahkan saja (aman kalau tidak ada)
            if (Schema::hasColumn('assignments', 'created_by')) $table->dropConstrainedForeignId('created_by');
            if (Schema::hasColumn('assignments', 'allow_file')) $table->dropColumn('allow_file');
            if (Schema::hasColumn('assignments', 'max_score'))  $table->dropColumn('max_score');
            if (Schema::hasColumn('assignments', 'due_date'))   $table->dropColumn('due_date');
            if (Schema::hasColumn('assignments', 'instructions')) $table->dropColumn('instructions');
            if (Schema::hasColumn('assignments', 'kelas_id'))   $table->dropConstrainedForeignId('kelas_id');
        });
    }
};
