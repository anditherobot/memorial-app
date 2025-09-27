<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_render(): void
    {
        $this->get('/')->assertOk();
        $this->get('/gallery')->assertOk();
        $this->get('/wishes')->assertOk();
        $this->get('/updates')->assertOk();
    }

    public function test_admin_routes_require_authentication(): void
    {
        $this->get('/admin')->assertStatus(302); // redirected to login
        $this->get('/admin/wishes')->assertStatus(302);
        $this->get('/admin/updates')->assertStatus(302);
        $this->get('/admin/gallery')->assertStatus(302);
    }
}

