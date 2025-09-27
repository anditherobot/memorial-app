<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'duration_seconds',
        'hash',
        'storage_path',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function derivatives()
    {
        return $this->hasMany(MediaDerivative::class);
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'attachable', 'media_attachables')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }

    public function wishes()
    {
        return $this->morphedByMany(Wish::class, 'attachable', 'media_attachables')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }
}

