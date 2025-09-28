<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // HAPUS tabel lama kalau ada
        Schema::dropIfExists('comments');

        // BUAT ULANG
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // relasi ke course_resources
            $table->foreignId('course_resource_id')
                  ->constrained('course_resources')
                  ->cascadeOnDelete();

            // relasi ke users
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // reply (self reference)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('comments')
                  ->nullOnDelete();

            $table->text('text');
            $table->unsignedTinyInteger('score')->nullable(); // 0–100 opsional
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('comments');
        Schema::enableForeignKeyConstraints();
    }
};

