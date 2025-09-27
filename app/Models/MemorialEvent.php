<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorialEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'title',
        'date',
        'time',
        'venue_name',
        'address',
        'contact_phone',
        'contact_email',
        'notes',
        'poster_media_id',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function posterMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'poster_media_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public static function getEventTypes(): array
    {
        return [
            'funeral' => 'Funeral Service',
            'viewing' => 'Viewing/Visitation',
            'burial' => 'Burial/Cemetery',
            'repass' => 'Repass/Reception',
        ];
    }

    public function getEventTypeDisplayAttribute(): string
    {
        return self::getEventTypes()[$this->event_type] ?? $this->event_type;
    }
}
