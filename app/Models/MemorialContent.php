<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemorialContent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'memorial_content';

    protected $fillable = [
        'content_type',
        'title',
        'content',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->updated_at = now();
        });
    }

    public function scopeByType($query, string $contentType)
    {
        return $query->where('content_type', $contentType);
    }

    public static function getContentTypes(): array
    {
        return [
            'bio' => 'Biography',
            'memorial_name' => 'Memorial Name',
            'memorial_dates' => 'Memorial Dates',
            'contact_info' => 'Contact Information',
        ];
    }

    public function getContentTypeDisplayAttribute(): string
    {
        return self::getContentTypes()[$this->content_type] ?? $this->content_type;
    }

    public static function findByType(string $contentType): ?self
    {
        return static::where('content_type', $contentType)->first();
    }

    public static function getOrCreateByType(string $contentType, array $defaults = []): self
    {
        return static::firstOrCreate(
            ['content_type' => $contentType],
            array_merge($defaults, ['content_type' => $contentType])
        );
    }
}
