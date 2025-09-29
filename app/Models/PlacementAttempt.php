<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementAttempt extends Model {
protected $fillable = [
  'user_id','test_id','status','score','level','started_at','ended_at','retake_available_at'
];
  protected $casts = ['started_at'=>'datetime','ended_at'=>'datetime','retake_available_at'=>'datetime'];
  public function answers(){ return $this->hasMany(PlacementAnswer::class,'attempt_id'); }
  public function test(){ return $this->belongsTo(PlacementTest::class,'test_id'); }
}
