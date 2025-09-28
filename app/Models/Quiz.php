<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'instructions',
        'time_limit',
        'shuffle',
    ];

    /* --------- Relations --------- */

    // setiap quiz bisa dipanggil dari CourseResource
    public function resource()
    {
        return $this->hasOne(CourseResource::class, 'quiz_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function quizSubmissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }
}
