<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementQuestion extends Model {
  protected $fillable = ['test_id','text','number']; // ganti order -> number
  public function options(){ return $this->hasMany(PlacementOption::class,'question_id'); }
}
