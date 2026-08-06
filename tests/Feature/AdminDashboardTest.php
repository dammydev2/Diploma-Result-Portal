<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_greets_the_logged_in_admin_by_name(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Jane Doe']));

        $response = $this->get(route('backpack.dashboard'));

        $response->assertOk();
        $response->assertSee('Welcome, Jane Doe');
    }

    public function test_a_super_admin_sees_all_three_stat_cards(): void
    {
        $this->actingAs(User::factory()->create());

        Student::factory()->count(2)->create();
        Programme::factory()->count(3)->create();

        $response = $this->get(route('backpack.dashboard'));

        $response->assertOk();
        $response->assertSee('Total students');
        $response->assertSee('Total programmes');
        $response->assertSee('Total admins');
        $response->assertSeeInOrder(['Total students', '2']);
        $response->assertSeeInOrder(['Total programmes', '3']);
    }

    public function test_a_regular_admin_only_sees_cards_for_permissions_they_have(): void
    {
        $this->actingAs(User::factory()->withPermissions([Permission::ManageStudents])->create());

        $response = $this->get(route('backpack.dashboard'));

        $response->assertOk();
        $response->assertSee('Total students');
        $response->assertDontSee('Total programmes');
        $response->assertDontSee('Total admins');
    }

    public function test_the_getting_started_widget_is_gone(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('backpack.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Getting Started');
    }
}
