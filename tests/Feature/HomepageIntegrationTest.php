<?php

namespace Tests\Feature;

use App\Models\MemorialContent;
use App\Models\MemorialEvent;
use App\Models\Post;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function homepage_displays_with_default_content()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Memorial');
        $response->assertSee('Cherished Memories');
        $response->assertSee('Updates', false);
        $response->assertSee('Events', false);
    }

    /** @test */
    public function homepage_displays_memorial_content_when_configured()
    {
        MemorialContent::create([
            'content_type' => 'memorial_name',
            'title' => 'John David Smith',
            'content' => 'John David Smith',
        ]);

        MemorialContent::create([
            'content_type' => 'bio',
            'title' => 'Life Story',
            'content' => 'John was a beloved father and husband who touched many lives through his kindness and generosity.',
        ]);

        MemorialContent::create([
            'content_type' => 'memorial_dates',
            'content' => "Birth: January 1, 1950\nPassing: December 31, 2023",
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('John David Smith');
        $response->assertSee('John was a beloved father');
        $response->assertSee('January 1, 1950');
        $response->assertSee('December 31, 2023');
    }

    /** @test */
    public function homepage_displays_upcoming_events()
    {
        MemorialEvent::create([
            'event_type' => 'funeral',
            'title' => 'Memorial Service',
            'date' => now()->addDays(7),
            'time' => '14:00',
            'venue_name' => 'Community Center',
            'address' => '123 Main St',
            'notes' => 'All are welcome to attend',
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Memorial Service');
        $response->assertSee('Community Center');
        $response->assertSee('123 Main St');
        $response->assertSee('All are welcome to attend');
        $response->assertSee('14:00');
        $response->assertSee('Funeral');
    }

    /** @test */
    public function homepage_displays_recent_updates()
    {
        Post::create([
            'title' => 'Memorial Announcement',
            'body' => 'We are planning a beautiful memorial service to celebrate their life.',
            'is_published' => true,
            'published_at' => now()->subDay(),
            'author_name' => 'Family',
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Memorial Announcement');
        $response->assertSee('We are planning a beautiful');
        $response->assertSee('Read More');
    }

    /** @test */
    public function homepage_displays_contact_information_when_configured()
    {
        MemorialContent::create([
            'content_type' => 'contact_info',
            'title' => 'Family Contact',
            'content' => "Phone: (555) 123-4567\nEmail: family@example.com",
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Contact Information');
        $response->assertSee('Family Contact');
        $response->assertSee('(555) 123-4567');
        $response->assertSee('family@example.com');
    }

    /** @test */
    public function homepage_shows_fallback_content_when_no_memorial_content_exists()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('We celebrate the life of a beloved friend');
        $response->assertSee('Guestbook & Wishes');
        $response->assertSee('Sign the Wishwall');
    }

    /** @test */
    public function homepage_displays_gallery_photos_when_available()
    {
        // Create a public media item
        $media = Media::create([
            'original_filename' => 'memorial-photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 102400,
            'width' => 800,
            'height' => 600,
            'storage_path' => 'media/originals/memorial-photo.jpg',
            'hash' => 'abc123',
            'is_public' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('memorial-photo.jpg');
    }
}