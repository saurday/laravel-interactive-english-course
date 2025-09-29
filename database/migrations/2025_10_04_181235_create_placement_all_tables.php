<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    /* =======================
     *  A. MASTER TEST & BANK SOAL
     * ======================= */
    Schema::create('placement_tests', function (Blueprint $t) {
      $t->id();
      $t->string('title')->default('English Placement Test');
      $t->unsignedSmallInteger('time_limit')->default(0); // 0 = no limit
      $t->boolean('is_active')->default(true);
      $t->timestamps();
    });

    Schema::create('placement_questions', function (Blueprint $t) {
      $t->id();
      $t->foreignId('test_id')->constrained('placement_tests')->cascadeOnDelete();
      $t->text('text');
      $t->unsignedSmallInteger('number')->default(0); // GANTI "order" -> "number"
      $t->timestamps();
      $t->unique(['test_id','number']);
    });

    Schema::create('placement_options', function (Blueprint $t) {
      $t->id();
      $t->foreignId('question_id')->constrained('placement_questions')->cascadeOnDelete();
      $t->char('label', 1); // 'A','B','C','D'
      $t->text('text');
      $t->boolean('is_correct')->default(false);
      $t->timestamps();
      $t->unique(['question_id','label']);
      $t->index('is_correct');
    });

    /* =======================
     *  B. LEVEL PLACEMENT (A1..C2) — terpisah dari kelas reguler
     * ======================= */
    Schema::create('placement_levels', function (Blueprint $t) {
      $t->id();
      $t->string('code', 8)->unique();     // 'A1'..'C2'
      $t->string('name');                  // "A1 BEGINNER", dst
      $t->text('description')->nullable();
      $t->boolean('is_active')->default(true);
      $t->timestamps();
    });

    // Konten per level (tanpa weeks)
    Schema::create('placement_level_contents', function (Blueprint $t) {
      $t->id();
      $t->foreignId('level_id')->constrained('placement_levels')->cascadeOnDelete();
      $t->unsignedSmallInteger('order')->default(0)->index();
      $t->string('type', 20);       // 'text','video','file','quiz','assignment' (opsional)
      $t->string('title');
      $t->longText('text')->nullable();
      $t->string('video_url')->nullable();
      $t->string('file_url')->nullable();
      $t->unsignedBigInteger('quiz_id')->nullable();
      $t->timestamps();
      $t->index(['level_id','order']);
    });

    // Enrol user → level
    Schema::create('placement_level_enrollments', function (Blueprint $t) {
      $t->id();
      $t->foreignId('level_id')->constrained('placement_levels')->cascadeOnDelete();
      $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $t->timestamp('joined_at')->useCurrent();
      $t->timestamps();
      $t->unique(['level_id','user_id']);
    });

    // Progress user pada konten level
    Schema::create('placement_level_progress', function (Blueprint $t) {
      $t->id();
      $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $t->foreignId('content_id')->constrained('placement_level_contents')->cascadeOnDelete();
      $t->timestamp('completed_at')->useCurrent();
      $t->timestamps();
      $t->unique(['user_id','content_id']);
    });

    /* =======================
     *  C. BANDING SKOR → LEVEL
     * ======================= */
    Schema::create('placement_bands', function (Blueprint $t) {
      $t->id();
      $t->string('level', 8)->unique();  // 'A1'..'C2' (kode band)
      $t->unsignedInteger('min_score');
      $t->unsignedInteger('max_score');
      $t->foreignId('target_level_id')->nullable()
        ->constrained('placement_levels')->nullOnDelete();
      $t->timestamps();
    });

    /* =======================
     *  D. ATTEMPT & ANSWER
     * ======================= */
    Schema::create('placement_attempts', function (Blueprint $t) {
      $t->id();
      $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $t->foreignId('test_id')->constrained('placement_tests')->cascadeOnDelete();
      $t->enum('status', ['started','submitted','aborted'])->default('started');
      $t->unsignedInteger('score')->default(0);
      $t->string('level', 8)->nullable(); // hasil band 'A1'..'C2'
      $t->timestamp('started_at')->useCurrent();
      $t->timestamp('ended_at')->nullable();
      $t->timestamp('retake_available_at')->nullable();
      $t->timestamps();
      $t->index(['user_id','status']);
    });

    Schema::create('placement_answers', function (Blueprint $t) {
      $t->id();
      $t->foreignId('attempt_id')->constrained('placement_attempts')->cascadeOnDelete();
      $t->foreignId('question_id')->constrained('placement_questions')->cascadeOnDelete();
      $t->foreignId('option_id')->nullable()->constrained('placement_options')->nullOnDelete();
      $t->timestamps();
      $t->unique(['attempt_id','question_id']);
    });

    /* =======================
     *  E. RINGKAS HASIL TERAKHIR PER USER
     * ======================= */
    Schema::create('user_placements', function (Blueprint $t) {
      $t->id();
      $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
      $t->string('level', 8);                // 'A1'..'C2'
      $t->unsignedInteger('score');
      $t->foreignId('attempt_id')->constrained('placement_attempts')->cascadeOnDelete();
      $t->foreignId('level_id')->nullable()  // simpan referensi level
        ->constrained('placement_levels')->nullOnDelete();
      $t->timestamp('tested_at');
      $t->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('user_placements');
    Schema::dropIfExists('placement_answers');
    Schema::dropIfExists('placement_attempts');
    Schema::dropIfExists('placement_bands');
    Schema::dropIfExists('placement_level_progress');
    Schema::dropIfExists('placement_level_enrollments');
    Schema::dropIfExists('placement_level_contents');
    Schema::dropIfExists('placement_levels');
    Schema::dropIfExists('placement_options');
    Schema::dropIfExists('placement_questions');
    Schema::dropIfExists('placement_tests');
  }
};
