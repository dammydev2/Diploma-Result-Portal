<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstitutionSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Dots ICT Institute of Technology',
            'address' => '12 Example Street, Lagos',
            'registrar_name' => 'Jane Doe',
            'registrar_title' => 'Registrar',
        ];
    }

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
        $response->assertSee(config('institution.registrar_name'));
    }

    public function test_institution_details_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), $this->validPayload());

        $response->assertRedirect(route('page.settings.institution'));

        $this->assertSame('Dots ICT Institute of Technology', Setting::get('institution_name'));
        $this->assertSame('12 Example Street, Lagos', Setting::get('institution_address'));
        $this->assertSame('Jane Doe', Setting::get('registrar_name'));
        $this->assertSame('Registrar', Setting::get('registrar_title'));
    }

    public function test_updated_institution_details_are_reflected_on_the_settings_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put(route('page.settings.institution.update'), $this->validPayload());

        $response = $this->get(route('page.settings.institution'));

        $response->assertSee('Dots ICT Institute of Technology');
        $response->assertSee('12 Example Street, Lagos');
        $response->assertSee('Jane Doe');
    }

    public function test_institution_update_requires_a_name(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_institution_update_requires_a_registrar_name_and_title(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'registrar_name' => '',
            'registrar_title' => '',
        ]);

        $response->assertSessionHasErrors(['registrar_name', 'registrar_title']);
    }

    public function test_institution_address_is_optional(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'address' => '',
        ]);

        $response->assertRedirect(route('page.settings.institution'));
        $this->assertSame('Dots ICT Institute of Technology', Setting::get('institution_name'));
    }

    public function test_the_background_image_can_be_uploaded_and_replaces_the_bundled_default(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'statement_background' => UploadedFile::fake()->image('background.jpg'),
        ]);

        $response->assertRedirect(route('page.settings.institution'));

        $path = Setting::get('statement_background_path');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_the_registrar_signature_can_be_uploaded_and_replaces_the_bundled_default(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'registrar_signature' => UploadedFile::fake()->image('signature.png'),
        ]);

        $response->assertRedirect(route('page.settings.institution'));

        $path = Setting::get('registrar_signature_path');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_uploading_a_new_background_image_deletes_a_previous_upload_with_a_different_extension(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'statement_background' => UploadedFile::fake()->image('first.jpg'),
        ]);
        $firstPath = Setting::get('statement_background_path');

        $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'statement_background' => UploadedFile::fake()->image('second.png'),
        ]);

        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists(Setting::get('statement_background_path'));
    }

    public function test_non_image_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.institution.update'), [
            ...$this->validPayload(),
            'statement_background' => UploadedFile::fake()->create('not-an-image.pdf', 100),
        ]);

        $response->assertSessionHasErrors(['statement_background']);
    }
}
