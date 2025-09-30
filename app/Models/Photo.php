<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_id',
        'status',
        'error_message',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    /**
     * Get the user that owns the photo.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the media associated with the photo.
     */
    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
