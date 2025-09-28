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
       Schema::create('assignment_submissions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('assignment_id');
        $table->unsignedBigInteger('mahasiswa_id');
        $table->text('answer_text')->nullable();
        $table->string('file_path')->nullable();
        $table->integer('score')->nullable();
        $table->timestamps();
        $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
        $table->foreign('mahasiswa_id')->references('id')->on('users')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
