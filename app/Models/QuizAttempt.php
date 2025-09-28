<?php

// app/Models/QuizAttempt.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model {
    public const STATUS_STARTED   = 'started';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_ABORTED   = 'aborted';
  protected $fillable = ['quiz_id','user_id','started_at','ended_at','time_left','status','score'];
  protected $casts = ['started_at'=>'datetime','ended_at'=>'datetime','time_left'=>'integer','score'=>'float'];
  public function quiz(){ return $this->belongsTo(Quiz::class); }
  public function answers(){ return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id'); }
}

