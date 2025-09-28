<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_resources', function (Blueprint $table) {
            if (!Schema::hasColumn('course_resources', 'quiz_id')) {
                $table->unsignedBigInteger('quiz_id')->nullable(); // <- HAPUS after('file_url')
                $table->foreign('quiz_id')
                      ->references('id')->on('quizzes')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_resources', function (Blueprint $table) {
            if (Schema::hasColumn('course_resources', 'quiz_id')) {
                $table->dropForeign(['quiz_id']);
                $table->dropColumn('quiz_id');
            }
        });
    }
};
