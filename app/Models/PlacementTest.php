<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/PlacementTest.php
class PlacementTest extends Model {
  protected $fillable = ['title','time_limit','is_active'];
  public function questions(){ return $this->hasMany(PlacementQuestion::class,'test_id'); }
}
