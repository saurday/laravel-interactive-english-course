<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPlacement extends Model {
  protected $fillable = ['user_id','level','score','attempt_id','level_id','tested_at'];
  protected $casts = ['tested_at'=>'datetime'];
}
