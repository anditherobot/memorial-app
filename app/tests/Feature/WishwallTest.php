<?php

namespace Tests\Feature;

use App\Models\Wish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishwallTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_wishes_page(): void
    {
        $response = $this->get('/wishes');
        $response->assertStatus(200);
        $response->assertSee('Share a Memory');
    }

    public function test_can_submit_a_wish_successfully(): void
    {
        $wishData = [
            'name' => 'John Doe',
            'message' => 'Rest in peace, you will be missed.',
            'email' => 'john@example.com',
            'honeypot' => '', // Empty honeypot (spam control)
        ];

        $response = $this->post('/wishes', $wishData);

        $response->assertStatus(200);
        $response->assertSee('Thank you for sharing your memory');

        // Check that wish was created with pending status
        $this->assertDatabaseHas('wishes', [
            'name' => 'John Doe',
            'message' => 'Rest in peace, you will be missed.',
            'email' => 'john@example.com',
            'approved' => false,
        ]);
    }

    public function test_honeypot_spam_protection_works(): void
    {
        $wishData = [
            'name' => 'Spam Bot',
            'message' => 'This is spam',
            'email' => 'spam@example.com',
            'honeypot' => 'filled', // Filled honeypot indicates spam
        ];

        $response = $this->post('/wishes', $wishData);

        // Should redirect back without creating the wish
        $response->assertRedirect();

        // Check that wish was NOT created
        $this->assertDatabaseMissing('wishes', [
            'name' => 'Spam Bot',
            'message' => 'This is spam',
        ]);
    }

    public function test_admin_can_view_pending_wishes(): void
    {
        // Create a pending wish
        $wish = Wish::factory()->create([
            'approved' => false,
            'name' => 'Jane Smith',
            'message' => 'Beautiful memories',
        ]);

        $response = $this->get('/admin/wishes?token=memorial_admin_2024_secure_token_xyz');

        $response->assertStatus(200);
        $response->assertSee('Jane Smith');
        $response->assertSee('Beautiful memories');
    }

    public function test_admin_can_approve_a_wish(): void
    {
        $wish = Wish::factory()->create(['approved' => false]);

        $response = $this->post("/admin/wishes/{$wish->id}/approve", [], [
            'X-Admin-Token' => 'memorial_admin_2024_secure_token_xyz'
        ]);

        $response->assertRedirect();

        // Check that wish is now approved
        $this->assertDatabaseHas('wishes', [
            'id' => $wish->id,
            'approved' => true,
        ]);
    }

    public function test_admin_can_delete_a_wish(): void
    {
        $wish = Wish::factory()->create();

        $response = $this->delete("/admin/wishes/{$wish->id}", [], [
            'X-Admin-Token' => 'memorial_admin_2024_secure_token_xyz'
        ]);

        $response->assertRedirect();

        // Check that wish was deleted
        $this->assertDatabaseMissing('wishes', [
            'id' => $wish->id,
        ]);
    }

    public function test_unauthorized_admin_access_is_blocked(): void
    {
        $response = $this->get('/admin/wishes');
        $response->assertStatus(403);

        $response = $this->get('/admin/wishes?token=wrong_token');
        $response->assertStatus(403);
    }

    public function test_wish_validation_works(): void
    {
        $response = $this->post('/wishes', [
            'name' => '', // Missing required field
            'message' => '',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['name', 'message', 'email']);
    }

    public function test_only_approved_wishes_are_shown_to_public(): void
    {
        $approvedWish = Wish::factory()->create([
            'approved' => true,
            'name' => 'Approved User',
            'message' => 'This should be visible',
        ]);

        $pendingWish = Wish::factory()->create([
            'approved' => false,
            'name' => 'Pending User',
            'message' => 'This should not be visible',
        ]);

        $response = $this->get('/wishes');

        $response->assertSee('Approved User');
        $response->assertSee('This should be visible');
        $response->assertDontSee('Pending User');
        $response->assertDontSee('This should not be visible');
    }
}