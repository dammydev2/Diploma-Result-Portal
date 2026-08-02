<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.settings.profile'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_profile_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.settings.profile'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put(route('page.settings.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('page.settings.profile'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_profile_update_requires_valid_data(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('page.settings.profile.update'), [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_the_account_can_be_deleted_with_the_correct_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete(route('page.settings.profile.destroy'), [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertModelMissing($user);
    }

    public function test_the_account_deletion_requires_the_correct_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete(route('page.settings.profile.destroy'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrorsIn('deleteAccount', ['password']);
        $this->assertAuthenticated();
        $this->assertModelExists($user);
    }
}
