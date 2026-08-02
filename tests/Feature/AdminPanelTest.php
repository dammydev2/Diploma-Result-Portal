<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_user_menu_links_to_profile_and_password_settings(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee(route('page.settings.profile'), false);
        $response->assertSee(route('page.settings.security'), false);
    }

    public function test_the_user_menu_logs_out_via_the_app_logout_route(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/admin/dashboard');

        $response->assertSee('action="'.route('logout').'"', false);
    }
}
