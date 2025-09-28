<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Week extends Model
{
    protected $fillable = ['kelas_id','week_number'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function resources()
    {
        return $this->hasMany(CourseResource::class)
                    ->orderBy('sort')->orderBy('id');
    }
}
