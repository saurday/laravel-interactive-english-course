<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('course_resources', function (Blueprint $table) {
        $table->enum('type', ['text', 'video', 'file', 'quiz', 'composite'])->default('text')->change();
    });
}

public function down()
{
    Schema::table('course_resources', function (Blueprint $table) {
        $table->enum('type', ['text', 'video', 'file', 'quiz'])->default('text')->change();
    });
}

};
