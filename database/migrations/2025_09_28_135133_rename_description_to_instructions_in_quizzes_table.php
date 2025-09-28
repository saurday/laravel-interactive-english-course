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
        // up()
        Schema::table('quizzes', function (Blueprint $table) {
            $table->renameColumn('description', 'instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down()
        Schema::table('quizzes', function (Blueprint $table) {
            $table->renameColumn('instructions', 'description');
        });
    }
};
