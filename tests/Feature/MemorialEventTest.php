<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MemorialEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemorialEventTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /** @test */
    public function memorial_events_index_displays_all_event_types()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('memorial.events.index'));

        $response->assertStatus(200);
        $response->assertSee('Memorial Events');
        $response->assertSee('Funeral Service');
        $response->assertSee('Viewing/Visitation');
        $response->assertSee('Burial/Cemetery');
        $response->assertSee('Repass/Reception');
    }

    /** @test */
    public function admin_can_create_memorial_event()
    {
        $this->actingAs($this->admin);

        $eventData = [
            'event_type' => 'funeral',
            'title' => 'Memorial Service for John Doe',
            'date' => '2024-12-25',
            'time' => '14:00',
            'venue_name' => 'Grace Chapel',
            'address' => '123 Main Street, Anytown, ST 12345',
            'contact_phone' => '555-123-4567',
            'contact_email' => 'contact@gracechapel.com',
            'notes' => 'Family requests donations to charity in lieu of flowers.',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.events.store'), $eventData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('memorial_events', $eventData);
    }

    /** @test */
    public function admin_can_update_memorial_event()
    {
        $this->actingAs($this->admin);

        $event = MemorialEvent::factory()->create([
            'event_type' => 'viewing',
            'title' => 'Original Title',
        ]);

        $updateData = [
            'event_type' => 'viewing',
            'title' => 'Updated Memorial Viewing',
            'date' => '2024-12-26',
            'time' => '10:00',
            'venue_name' => 'Updated Venue',
            'address' => 'Updated Address',
            'contact_phone' => '555-987-6543',
            'contact_email' => 'updated@venue.com',
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->put(route('memorial.events.update', $event), $updateData);

        $response->assertStatus(302);

        // Check individual fields since date is stored as datetime
        $this->assertDatabaseHas('memorial_events', [
            'id' => $event->id,
            'event_type' => 'viewing',
            'title' => 'Updated Memorial Viewing',
            'time' => '10:00',
            'venue_name' => 'Updated Venue',
            'address' => 'Updated Address',
            'contact_phone' => '555-987-6543',
            'contact_email' => 'updated@venue.com',
            'notes' => 'Updated notes',
        ]);

        // Check date separately
        $updatedEvent = $event->fresh();
        $this->assertEquals('2024-12-26', $updatedEvent->date->format('Y-m-d'));
    }

    /** @test */
    public function admin_can_delete_memorial_event()
    {
        $this->actingAs($this->admin);

        $event = MemorialEvent::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->delete(route('memorial.events.destroy', $event));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('memorial_events', ['id' => $event->id]);
    }

    /** @test */
    public function memorial_event_requires_valid_event_type()
    {
        $this->actingAs($this->admin);

        $eventData = [
            'event_type' => 'invalid_type',
            'title' => 'Test Event',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.events.store'), $eventData);

        $response->assertStatus(302);        $response->assertSessionHasErrors('event_type');
    }

    /** @test */
    public function memorial_event_can_have_poster_image()
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $file = UploadedFile::fake()->image('poster.jpg', 800, 600);

        $eventData = [
            'event_type' => 'funeral',
            'title' => 'Memorial Service',
            'poster' => $file,
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.events.store'), $eventData);

        $response->assertStatus(302);

        $event = MemorialEvent::first();
        $this->assertNotNull($event->poster_media_id);
        $this->assertInstanceOf(Media::class, $event->posterMedia);
    }

    /** @test */
    public function non_admin_cannot_access_memorial_events()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('memorial.events.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_memorial_events()
    {
        $response = $this->get(route('memorial.events.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function memorial_event_validates_required_fields()
    {
        $this->actingAs($this->admin);

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.events.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['event_type', 'title']);
    }

    /** @test */
    public function memorial_event_validates_date_format()
    {
        $this->actingAs($this->admin);

        $eventData = [
            'event_type' => 'funeral',
            'title' => 'Test Event',
            'date' => 'invalid-date',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.events.store'), $eventData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('date');
    }

    /** @test */
    public function memorial_event_validates_time_format()
    {
        $this->actingAs($this->admin);

        $eventData = [
            'event_type' => 'funeral',
            'title' => 'Test Event',
            'time' => 'invalid-time',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.events.store'), $eventData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('time');
    }

    /** @test */
    public function memorial_event_validates_email_format()
    {
        $this->actingAs($this->admin);

        $eventData = [
            'event_type' => 'funeral',
            'title' => 'Test Event',
            'contact_email' => 'invalid-email',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.events.store'), $eventData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('contact_email');
    }
}