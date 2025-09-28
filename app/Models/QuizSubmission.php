<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizSubmission extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_id', 'mahasiswa_id', 'score'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
