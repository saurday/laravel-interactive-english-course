<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlacementBandSeeder extends Seeder
{
    public function run(): void
    {
        // ambil id level per kode
        $levelIds = DB::table('placement_levels')->pluck('id','code');

        DB::table('placement_bands')->upsert([
            ['level'=>'A1', 'min_score'=>0,  'max_score'=>15, 'target_level_id'=>$levelIds['A1'] ?? null],
            ['level'=>'A2', 'min_score'=>16, 'max_score'=>30, 'target_level_id'=>$levelIds['A2'] ?? null],
            ['level'=>'B1', 'min_score'=>31, 'max_score'=>45, 'target_level_id'=>$levelIds['B1'] ?? null],
            ['level'=>'B2', 'min_score'=>46, 'max_score'=>60, 'target_level_id'=>$levelIds['B2'] ?? null],
            ['level'=>'C1', 'min_score'=>61, 'max_score'=>75, 'target_level_id'=>$levelIds['C1'] ?? null],
            ['level'=>'C2', 'min_score'=>76, 'max_score'=>100,'target_level_id'=>$levelIds['C2'] ?? null],
        ], ['level'], ['min_score','max_score','target_level_id']);
    }
}
