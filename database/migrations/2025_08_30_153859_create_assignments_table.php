<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    if (Schema::hasTable('assignments')) {
        return; // tabel sudah ada -> jangan create lagi
    }

    Schema::create('assignments', function (Blueprint $table) {
        // SAMAKAN dengan skema nyata kamu (lihat screenshot yg kamu kirim)
        $table->id();
        $table->foreignId('kelas_id')->nullable()->index();
        $table->string('title');
        $table->text('instructions')->nullable();
        $table->dateTime('due_date')->nullable();
        $table->integer('max_score')->default(100);
        $table->boolean('allow_file')->default(true);
        $table->foreignId('created_by')->nullable()->index();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('assignments');
}
};
