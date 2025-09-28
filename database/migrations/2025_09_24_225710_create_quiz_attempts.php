<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('quiz_attempts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->timestamp('started_at')->nullable();
      $table->timestamp('ended_at')->nullable();
      $table->integer('time_left')->nullable(); // detik; null = tanpa batas
      $table->enum('status', ['started','submitted','graded'])->default('started');
      $table->decimal('score', 5, 2)->nullable(); // 0..100
      $table->timestamps();
      $table->unique(['quiz_id','user_id','status']); // maksimal 1 yg "started" per user
    });
  }
  public function down(): void { Schema::dropIfExists('quiz_attempts'); }
};
