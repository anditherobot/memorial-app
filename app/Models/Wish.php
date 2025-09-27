<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'message', 'submitted_ip', 'is_approved'
    ];

    public function media()
    {
        return $this->morphToMany(Media::class, 'attachable', 'media_attachables')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }
}

