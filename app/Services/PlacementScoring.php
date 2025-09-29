<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PlacementScoring
{
    // fallback hard-coded (kalau tabel bands kosong)
   public static function mapScoreToLevel(int $score): string {
  if ($score <= 15) return 'A1';
  if ($score <= 30) return 'A2';
  if ($score <= 45) return 'B1';
  if ($score <= 60) return 'B2';
  if ($score <= 75) return 'C1';
  return 'C2';
}


    // prefer: baca dari tabel placement_bands
    public static function resolveBand(int $score): ?object
    {
        return DB::table('placement_bands')
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first(); // {level,min_score,max_score,target_class_id,...} atau null
    }
}
