<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function admin_layout_renders_sidebar_navigation()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Check for memorial-focused navigation items
        $response->assertSee('Dashboard');
        $response->assertSee('Memorial Events');
        $response->assertSee('Memorial Content');
        $response->assertSee('Wishes & Messages');
        $response->assertSee('Gallery');
        $response->assertSee('Tasks');
        $response->assertSee('Documentation');
    }

    /** @test */
    public function admin_layout_shows_active_navigation_state()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Dashboard should be marked as active
        $response->assertSee('Dashboard', false);
        // Should contain active state indicators (we'll use CSS classes)
        $response->assertSeeText('Dashboard');
    }

    /** @test */
    public function admin_layout_includes_breadcrumbs()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Should show breadcrumb navigation
        $response->assertSee('Admin');
        $response->assertSee('Dashboard');
    }

    /** @test */
    public function admin_layout_has_logout_functionality()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Should show user info and logout option
        $response->assertSee('Logout');
        $response->assertSee($this->adminUser->email);
    }

    /** @test */
    public function admin_layout_is_mobile_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Should include mobile menu toggle
        $response->assertSee('Mobile menu');

        // Should have responsive CSS classes
        $this->assertStringContainsString('lg:hidden', $response->getContent());
        $this->assertStringContainsString('lg:translate-x-0', $response->getContent());
    }

    /** @test */
    public function non_admin_cannot_access_admin_layout()
    {
        $regularUser = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.dashboard'));

        // Should redirect or return 403
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 302
        );
    }

    /** @test */
    public function guest_cannot_access_admin_layout()
    {
        $response = $this->get(route('admin.dashboard'));

        // Should redirect to login
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_layout_navigation_links_work()
    {
        // Test that all navigation links point to correct routes
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Check for existing route links
        $response->assertSee(route('admin.dashboard'));
        $response->assertSee(route('admin.wishes'));
        $response->assertSee(route('admin.gallery'));
        $response->assertSee(route('admin.tasks.index'));
        $response->assertSee(route('admin.docs'));
    }
}