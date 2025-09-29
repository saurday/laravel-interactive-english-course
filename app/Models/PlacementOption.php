<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementOption extends Model {
  protected $fillable = ['question_id','label','text','is_correct']; // tambah label
}
