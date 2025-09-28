<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_resources', function (Blueprint $t) {
            $t->id();
            $t->foreignId('week_id')->constrained('weeks')->cascadeOnDelete();
            $t->enum('type', ['text', 'video', 'file', 'quiz', 'composite'])->default('text');
            $t->string('title')->nullable();     // judul materi (opsional, wajib untuk text)
            $t->text('text')->nullable();        // konten teks
            $t->string('video_url')->nullable(); // link video
            $t->string('file_path')->nullable(); // path file di storage
            $t->unsignedInteger('sort')->default(0);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_resources');
    }
};
