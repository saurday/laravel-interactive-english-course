<?php

// database/migrations/2025_09_23_000001_alter_quizzes_add_meta.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('quizzes', function (Blueprint $table) {
            // jadikan opsional agar bisa buat quiz tanpa material terlebih dulu

            // meta yg dibutuhkan frontend
            $table->text('description')->nullable()->after('title');
            $table->integer('time_limit')->default(0)->after('description'); // menit; 0 = no limit
            $table->boolean('shuffle')->default(false)->after('time_limit');
        });
    }

    public function down(): void {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['description','time_limit','shuffle']);
        });
    }
};
