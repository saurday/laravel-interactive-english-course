<?php

// database/migrations/xxxx_xx_xx_rename_order_to_sort_order_on_placement_level_contents.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('placement_level_contents', function (Blueprint $t) {
            $t->renameColumn('order', 'sort_order');
        });
    }
    public function down(): void
    {
        Schema::table('placement_level_contents', function (Blueprint $t) {
            $t->renameColumn('sort_order', 'order');
        });
    }
};
