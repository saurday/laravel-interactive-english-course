<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementBand extends Model {
  protected $fillable = ['level','min_score','max_score','target_level_id']; // ganti target_class_id
}

