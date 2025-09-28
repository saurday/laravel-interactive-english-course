<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('weeks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->unsignedInteger('week_number');
            $t->timestamps();

            $t->unique(['kelas_id', 'week_number']); // 1 kelas tidak boleh ada week ganda
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weeks');
    }
};
