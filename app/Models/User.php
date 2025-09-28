<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function quizSubmissions()
    {
        return $this->hasMany(QuizSubmission::class, 'mahasiswa_id');
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'mahasiswa_id');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class, 'mahasiswa_id');
    }


    // Jika user adalah dosen
    public function kelas_dosen()
    {
        return $this->hasMany(Kelas::class, 'dosen_id');
    }

    // Jika user adalah mahasiswa
    public function kelas_mahasiswa()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_mahasiswa', 'mahasiswa_id', 'kelas_id')
            ->withPivot('joined_at');
    }

    // App\Models\User.php
    public function kelasDiikuti()
    {
        return $this->belongsToMany(
            \App\Models\Kelas::class,
            'kelas_mahasiswa',
            'mahasiswa_id',  // FK ke users
            'kelas_id'       // FK ke kelas
        )->withPivot('joined_at');
    }

    public function classes()
{
    return $this->belongsToMany(
        \App\Models\Kelas::class,
        'kelas_mahasiswa',
        'mahasiswa_id',
        'kelas_id'
    )->withPivot('joined_at')
     ->withTimestamps();
}


}
