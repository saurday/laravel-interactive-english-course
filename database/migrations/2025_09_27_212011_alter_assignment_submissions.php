// database/migrations/2025_09_28_000000_alter_assignment_submissions.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // 1 submission per assignment per mahasiswa
            $table->unique(['assignment_id', 'mahasiswa_id'], 'uniq_assignment_student');

            // waktu submit & penilaian + feedback
            $table->timestamp('submitted_at')->nullable()->after('file_path');
            $table->timestamp('graded_at')->nullable()->after('submitted_at');
            $table->text('feedback')->nullable()->after('score');

            // (opsional) perpanjang panjang path file
            $table->string('file_path', 2048)->change();
        });
    }

    public function down(): void {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropUnique('uniq_assignment_student');
            $table->dropColumn(['submitted_at', 'graded_at', 'feedback']);
            // tidak revert panjang kolom file_path supaya aman
        });
    }
};
