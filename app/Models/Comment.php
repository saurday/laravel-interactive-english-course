<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = ['course_resource_id','user_id','parent_id','text','score'];

    protected $with = ['user']; // agar nama user ikut ke JSON

    public function resource(): BelongsTo {
        return $this->belongsTo(CourseResource::class, 'course_resource_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }
}
