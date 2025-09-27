<?php

namespace Tests\Feature;

use App\Models\Wish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishwallTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishwall_submission_creates_pending_record(): void
    {
        $payload = [
            'name' => 'Guest User',
            'message' => 'Sharing a fond memory.',
            'website' => '', // honeypot empty
        ];

        $response = $this->withHeader('HX-Request', 'true')->post('/wishes', $payload);

        $response->assertOk(); // partial returned for HTMX
        $this->assertDatabaseHas('wishes', [
            'name' => 'Guest User',
            'message' => 'Sharing a fond memory.',
            'is_approved' => 0,
        ]);
    }

    public function test_wishwall_displays_only_approved(): void
    {
        Wish::factory()->create([
            'name' => 'Approved',
            'message' => 'Visible wish',
            'is_approved' => true,
        ]);
        Wish::factory()->create([
            'name' => 'Pending',
            'message' => 'Hidden wish',
            'is_approved' => false,
        ]);

        $res = $this->get('/wishes');
        $res->assertOk();
        $res->assertSee('Visible wish');
        $res->assertDontSee('Hidden wish');
    }

    public function test_rate_limit_on_submit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/wishes', [
                'name' => 'User '.$i,
                'message' => 'Hello '.$i,
                'website' => '',
            ])->assertStatus(302)->assertSessionHas('status');
        }

        $this->post('/wishes', [
            'name' => 'User 6',
            'message' => 'Hello 6',
            'website' => '',
        ])->assertStatus(429);
    }
}

