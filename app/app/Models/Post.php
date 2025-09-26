<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'body', 'is_published', 'published_at', 'author_name'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function media()
    {
        return $this->morphToMany(Media::class, 'attachable', 'media_attachables')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }
}

