<?php

namespace Tests\Feature;

use App\Models\MemorialContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemorialContentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /** @test */
    public function memorial_content_index_displays_all_content_types()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('memorial.content.index'));

        $response->assertStatus(200);
        $response->assertSee('Memorial Content');
        $response->assertSee('Biography');
        $response->assertSee('Contact Information');
        $response->assertSee('Memorial Name');
        $response->assertSee('Memorial Dates');
    }

    /** @test */
    public function admin_can_create_memorial_content()
    {
        $this->actingAs($this->admin);

        $contentData = [
            'content_type' => 'bio',
            'title' => 'Life Story',
            'content' => 'John Doe was a beloved father, husband, and friend who touched many lives...',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.content.store'), $contentData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('memorial_content', $contentData);
    }

    /** @test */
    public function admin_can_update_memorial_content()
    {
        $this->actingAs($this->admin);

        $content = MemorialContent::factory()->create([
            'content_type' => 'memorial_name',
            'title' => 'Original Name',
            'content' => 'John Doe',
        ]);

        $updateData = [
            'content_type' => 'memorial_name',
            'title' => 'Full Memorial Name',
            'content' => 'John David Doe',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->put(route('memorial.content.update', $content), $updateData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('memorial_content', $updateData);
    }

    /** @test */
    public function admin_can_delete_memorial_content()
    {
        $this->actingAs($this->admin);

        $content = MemorialContent::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->delete(route('memorial.content.destroy', $content));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('memorial_content', ['id' => $content->id]);
    }

    /** @test */
    public function memorial_content_requires_valid_content_type()
    {
        $this->actingAs($this->admin);

        $contentData = [
            'content_type' => 'invalid_type',
            'title' => 'Test Content',
            'content' => 'Some content',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.content.store'), $contentData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('content_type');
    }

    /** @test */
    public function memorial_content_validates_required_fields()
    {
        $this->actingAs($this->admin);

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.content.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['content_type']);
    }

    /** @test */
    public function memorial_content_can_be_retrieved_by_type()
    {
        $this->actingAs($this->admin);

        MemorialContent::factory()->create(['content_type' => 'bio']);
        MemorialContent::factory()->create(['content_type' => 'memorial_name']);

        $response = $this->get(route('memorial.content.show', 'bio'));

        $response->assertStatus(200);
    }

    /** @test */
    public function memorial_content_enforces_unique_content_type()
    {
        $this->actingAs($this->admin);

        // Create first content of type 'bio'
        MemorialContent::factory()->create(['content_type' => 'bio']);

        // Try to create another content of same type
        $contentData = [
            'content_type' => 'bio',
            'title' => 'Another Bio',
            'content' => 'Different content',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.content.store'), $contentData);

        $response->assertStatus(302);        $response->assertSessionHasErrors('content_type');
    }

    /** @test */
    public function non_admin_cannot_access_memorial_content()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('memorial.content.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_memorial_content()
    {
        $response = $this->get(route('memorial.content.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function memorial_content_can_have_empty_title()
    {
        $this->actingAs($this->admin);

        $contentData = [
            'content_type' => 'contact_info',
            'title' => null,
            'content' => 'Phone: 555-123-4567',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.content.store'), $contentData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('memorial_content', $contentData);
    }

    /** @test */
    public function memorial_content_can_have_empty_content()
    {
        $this->actingAs($this->admin);

        $contentData = [
            'content_type' => 'memorial_dates',
            'title' => 'Important Dates',
            'content' => null,
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('memorial.content.store'), $contentData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('memorial_content', $contentData);
    }

    /** @test */
    public function memorial_content_supports_all_valid_types()
    {
        $this->actingAs($this->admin);

        $validTypes = ['bio', 'memorial_name', 'memorial_dates', 'contact_info'];

        foreach ($validTypes as $index => $type) {
            $contentData = [
                'content_type' => $type,
                'title' => "Test {$type}",
                'content' => "Content for {$type}",
            ];

            $contentData = [
                'content_type' => $type,
                'title' => "Test {$type}",
                'content' => "Content for {$type}",
            ];

            $response = $this->actingAs($this->admin)
                ->withHeaders([
                    'X-CSRF-TOKEN' => csrf_token(),
                ])->post(route('memorial.content.store'), $contentData);
            $response->assertStatus(302);
            $this->assertDatabaseHas('memorial_content', $contentData);
        }

        $this->assertDatabaseCount('memorial_content', count($validTypes));
    }
}