<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaDerivative extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id', 'type', 'storage_path', 'width', 'height', 'size_bytes'
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}

