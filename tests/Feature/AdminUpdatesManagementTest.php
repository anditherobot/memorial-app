<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUpdatesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin()
    {
        return User::factory()->create([
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_create_update_with_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $payload = [
            'title' => 'New Update',
            'body' => '<p>Details</p>',
            'is_published' => 1,
            'image' => UploadedFile::fake()->image('cover.jpg', 800, 600),
        ];

        $this->actingAs($admin)
            ->post(route('admin.updates.store'), $payload)
            ->assertRedirect(route('admin.updates.index'));

        $this->assertDatabaseHas('posts', [
            'title' => 'New Update',
            'is_published' => 1,
        ]);
    }

    public function test_admin_can_edit_and_delete_update(): void
    {
        $admin = $this->admin();
        $post = Post::factory()->create(['title' => 'Before', 'is_published' => false]);

        $this->actingAs($admin)
            ->put(route('admin.updates.update', $post), [
                'title' => 'After',
                'body' => '<p>Changed</p>',
                'is_published' => 1,
            ])->assertRedirect(route('admin.updates.index'));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'After',
            'is_published' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.updates.destroy', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}

