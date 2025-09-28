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
         Schema::create('questions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('quiz_id');
        $table->text('question_text');
        $table->enum('type', ['multiple_choice', 'drag_drop', 'matching', 'short_answer', 'ordering', 'categorizing']);
        $table->timestamps();
        $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
