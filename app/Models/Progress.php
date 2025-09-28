<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progress'; // tabel kamu memang bernama 'progress'
    protected $fillable = ['user_id','course_resource_id','completed','completed_at'];
    protected $casts = ['completed'=>'boolean','completed_at'=>'datetime'];

    public function resource()
    {
        return $this->belongsTo(CourseResource::class, 'course_resource_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
