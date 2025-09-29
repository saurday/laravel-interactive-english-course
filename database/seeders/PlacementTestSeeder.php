<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlacementTestSeeder extends Seeder
{
    public function run(): void
    {

    DB::table('placement_tests')->delete();


        // 1) buat test aktif
        $testId = DB::table('placement_tests')->insertGetId([
            'title'      => 'Language Hub Placement Test',
            'time_limit' => 30,     // 0 = no limit, sesuai migrasi
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2) load bank soal dari JSON
        $path = database_path('seeders/data/placement_test.json');
        $items = json_decode(file_get_contents($path), true) ?? [];

        foreach ($items as $row) {
            $qid = DB::table('placement_questions')->insertGetId([
                'test_id'    => $testId,
                'number'     => (int)$row['no'],  // kolom 'number' (bukan 'order')
                'text'       => $row['q'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (['A','B','C','D'] as $label) {
                DB::table('placement_options')->insert([
                    'question_id' => $qid,
                    'label'       => $label,                 // A/B/C/D
                    'text'        => $row[$label],
                    'is_correct'  => ($label === $row['ans']),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
