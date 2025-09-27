<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\MemorialEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemorialEventModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function memorial_event_has_correct_fillable_attributes()
    {
        $event = new MemorialEvent();

        $expectedFillable = [
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

        $this->assertEquals($expectedFillable, $event->getFillable());
    }

    /** @test */
    public function memorial_event_has_correct_casts()
    {
        $event = new MemorialEvent();

        $expectedCasts = [
            'id' => 'int',
            'date' => 'date',
            'time' => 'datetime:H:i',
            'is_active' => 'boolean',
        ];

        $this->assertEquals($expectedCasts, $event->getCasts());
    }

    /** @test */
    public function memorial_event_belongs_to_poster_media()
    {
        $media = Media::factory()->create();
        $event = MemorialEvent::factory()->create(['poster_media_id' => $media->id]);

        $this->assertInstanceOf(Media::class, $event->posterMedia);
        $this->assertEquals($media->id, $event->posterMedia->id);
    }

    /** @test */
    public function memorial_event_can_have_null_poster_media()
    {
        $event = MemorialEvent::factory()->create(['poster_media_id' => null]);

        $this->assertNull($event->posterMedia);
    }

    /** @test */
    public function memorial_event_has_valid_event_types()
    {
        $validTypes = ['funeral', 'viewing', 'burial', 'repass'];

        foreach ($validTypes as $type) {
            $event = MemorialEvent::factory()->create(['event_type' => $type]);
            $this->assertEquals($type, $event->event_type);
        }
    }

    /** @test */
    public function memorial_event_scope_active_returns_only_active_events()
    {
        MemorialEvent::factory()->count(3)->create(['is_active' => true]);
        MemorialEvent::factory()->count(2)->create(['is_active' => false]);

        $activeEvents = MemorialEvent::active()->get();

        $this->assertCount(3, $activeEvents);
        foreach ($activeEvents as $event) {
            $this->assertTrue($event->is_active);
        }
    }

    /** @test */
    public function memorial_event_scope_by_type_filters_by_event_type()
    {
        MemorialEvent::factory()->count(2)->create(['event_type' => 'funeral']);
        MemorialEvent::factory()->count(1)->create(['event_type' => 'viewing']);
        MemorialEvent::factory()->count(1)->create(['event_type' => 'burial']);

        $funeralEvents = MemorialEvent::byType('funeral')->get();
        $viewingEvents = MemorialEvent::byType('viewing')->get();

        $this->assertCount(2, $funeralEvents);
        $this->assertCount(1, $viewingEvents);

        foreach ($funeralEvents as $event) {
            $this->assertEquals('funeral', $event->event_type);
        }
    }

    /** @test */
    public function memorial_event_has_formatted_date_accessor()
    {
        $event = MemorialEvent::factory()->create(['date' => '2024-12-25']);

        $this->assertEquals('2024-12-25', $event->date->format('Y-m-d'));
    }

    /** @test */
    public function memorial_event_has_formatted_time_accessor()
    {
        $event = MemorialEvent::factory()->create(['time' => '14:30:00']);

        $this->assertEquals('14:30', $event->time->format('H:i'));
    }

    /** @test */
    public function memorial_event_defaults_to_active()
    {
        $event = MemorialEvent::factory()->create();

        $this->assertTrue($event->is_active);
    }

    /** @test */
    public function memorial_event_can_be_inactive()
    {
        $event = MemorialEvent::factory()->create(['is_active' => false]);

        $this->assertFalse($event->is_active);
    }
}