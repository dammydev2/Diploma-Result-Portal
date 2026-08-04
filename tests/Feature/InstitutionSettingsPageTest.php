<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.settings.institution'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_institution_settings_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.settings.institution'));

        $response->assertOk();
    }

    public function test_the_page_falls_back_to_config_defaults_until_a_setting_is_saved(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.settings.institution'));

        $response->assertOk();
        $response->assertSee(config('institution.name'));
    }

    public function test_institution_details_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            'name' => 'Dots ICT Institute of Technology',
            'address' => '12 Example Street, Lagos',
        ]);

        $response->assertRedirect(route('page.settings.institution'));

        $this->assertSame('Dots ICT Institute of Technology', Setting::get('institution_name'));
        $this->assertSame('12 Example Street, Lagos', Setting::get('institution_address'));
    }

    public function test_updated_institution_details_are_reflected_on_the_settings_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put(route('page.settings.institution.update'), [
            'name' => 'Dots ICT Institute of Technology',
            'address' => '12 Example Street, Lagos',
        ]);

        $response = $this->get(route('page.settings.institution'));

        $response->assertSee('Dots ICT Institute of Technology');
        $response->assertSee('12 Example Street, Lagos');
    }

    public function test_institution_update_requires_a_name(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            'name' => '',
            'address' => 'Somewhere',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_institution_address_is_optional(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            'name' => 'Dots ICT Institute of Technology',
            'address' => '',
        ]);

        $response->assertRedirect(route('page.settings.institution'));
        $this->assertSame('Dots ICT Institute of Technology', Setting::get('institution_name'));
    }
}
