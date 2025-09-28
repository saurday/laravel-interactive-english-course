<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('quiz_attempt_answers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
      $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
      $table->foreignId('option_id')->nullable()->constrained('options')->nullOnDelete(); // utk MCQ
      $table->text('text_answer')->nullable(); // utk short-answer (kalau nanti dipakai)
      $table->boolean('is_correct')->nullable();
      $table->timestamps();
      $table->unique(['attempt_id','question_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('quiz_attempt_answers'); }
};
