<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        if (!Schema::hasTable('progress')) {  // <-- GUARD: kalau sudah ada, skip create
            Schema::create('progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mahasiswa_id');
                $table->unsignedInteger('week');
                $table->unsignedInteger('percentage')->default(0);
                $table->timestamps();

                $table->foreign('mahasiswa_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

  
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
