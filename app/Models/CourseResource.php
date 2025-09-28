<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class CourseResource extends Model
{
    // daftar kolom yang bisa diisi (fillable)
    protected $fillable = [
        'week_id',
        'type',
        'title',
        'text',
        'video_url',
        'file_path',
        'quiz_id',
        'created_by',
        'assignment_id',   // ← TAMBAHKAN INI
    ];

    // accessor otomatis ikut muncul di JSON
    protected $appends = ['file_url', 'week_number'];

    /* --------- Relations --------- */

    public function week()
    {
        return $this->belongsTo(Week::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->orderBy('created_at');
    }

    /* --------- Accessors --------- */

    // supaya frontend terima URL lengkap (http://...)
    public function getFileUrlAttribute(): ?string
    {
        $path = $this->file_path;
        if (!$path) return null;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path; // jika eksternal URL
        }

        return Storage::url(ltrim($path, '/'));
    }

    public function getWeekNumberAttribute(): ?int
    {
        return optional($this->week)->week_number;
    }

    /* --------- Casts --------- */
    protected $casts = [
        'quiz_id'       => 'integer',
        'assignment_id' => 'integer', // opsional tapi bagus ada
    ];

    public function progresses()
    {
        return $this->hasMany(Progress::class, 'course_resource_id');
    }

    public function assignment()
    {
        return $this->belongsTo(\App\Models\Assignment::class, 'assignment_id');
    }
}
