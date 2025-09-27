<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_wish(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);
        $wish = Wish::factory()->create(['is_approved' => false]);

        $this->actingAs($admin)
            ->post(route('admin.wishes.approve', $wish))
            ->assertRedirect();

        $this->assertTrue($wish->fresh()->is_approved);
    }

    public function test_non_admin_cannot_approve_wish(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
        $wish = Wish::factory()->create(['is_approved' => false]);

        $this->actingAs($user)
            ->post(route('admin.wishes.approve', $wish))
            ->assertStatus(403);
    }
}

