<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admin_forbidden_from_admin(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $this->actingAs($user)->get('/admin')->assertStatus(403);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}

