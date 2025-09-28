<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;


class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'mahasiswa_id',
        'answer_text',
        'file_path',
        'score',
        'feedback',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
        'score'        => 'integer',
    ];

    protected $appends = ['file_url'];

    // URL publik untuk file
    public function getFileUrlAttribute(): ?string
{
    return $this->file_path
        ? asset('storage/'.$this->file_path)
        : null;
}


    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }



}
