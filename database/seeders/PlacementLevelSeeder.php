<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlacementLevelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('placement_levels')->upsert([
            ['code'=>'A1', 'name'=>'A1 BEGINNER',           'is_active'=>1],
            ['code'=>'A2', 'name'=>'A2 PRE INTERMEDIATE',   'is_active'=>1],
            ['code'=>'B1', 'name'=>'B1 INTERMEDIATE',       'is_active'=>1],
            ['code'=>'B2', 'name'=>'B2 UPPER INTERMEDIATE', 'is_active'=>1],
            ['code'=>'C1', 'name'=>'C1 ADVANCE',            'is_active'=>1],
            ['code'=>'C2', 'name'=>'C2 PROFICIENCY',        'is_active'=>1],
        ], ['code'], ['name','is_active']);
    }
}
