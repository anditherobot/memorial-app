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

    public function photo()
    {
        return $this->hasOne(Photo::class);
    }
}

