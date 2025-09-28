<?php

// app/Models/QuizAttemptAnswer.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class QuizAttemptAnswer extends Model {
  protected $fillable = ['attempt_id','question_id','option_id','text_answer','is_correct'];
  protected $casts = ['is_correct'=>'boolean'];
  public function attempt(){ return $this->belongsTo(QuizAttempt::class, 'attempt_id'); }
  public function question(){ return $this->belongsTo(Question::class); }
  public function option(){ return $this->belongsTo(Option::class); }
}
