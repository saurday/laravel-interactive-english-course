<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kelas_id',
        'title',
        'instructions',
        'due_date',
        'max_score',
        'allow_file',
        'created_by',
    ];

    protected $casts = [
        'allow_file' => 'boolean',
        'due_date'   => 'datetime',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courseResources()
    {
        return $this->hasMany(CourseResource::class, 'assignment_id');
    }

    public function submissions()
{
    return $this->hasMany(\App\Models\AssignmentSubmission::class);
}
}
