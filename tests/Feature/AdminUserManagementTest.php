<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.admins.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_admins_cannot_access_the_admins_page(): void
    {
        $this->actingAs(User::factory()->withPermissions(Permission::cases())->create());

        $response = $this->get(route('page.admins.index'));

        $response->assertForbidden();
    }

    public function test_super_admins_can_view_the_admins_list(): void
    {
        $this->actingAs(User::factory()->create());

        $other = User::factory()->create(['name' => 'Jane Doe']);

        $response = $this->get(route('page.admins.index'));

        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_a_super_admin_can_create_a_regular_admin_with_specific_permissions(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('page.admins.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'permissions' => [Permission::ManageStudents->value, Permission::ScoreEntry->value],
        ]);

        $response->assertRedirect(route('page.admins.index'));

        $created = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertFalse($created->is_super_admin);
        $this->assertTrue($created->hasPermission(Permission::ManageStudents));
        $this->assertTrue($created->hasPermission(Permission::ScoreEntry));
        $this->assertFalse($created->hasPermission(Permission::ManageProgrammes));
    }

    public function test_a_super_admin_can_create_another_super_admin(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('page.admins.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_super_admin' => '1',
        ]);

        $created = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertTrue($created->is_super_admin);
    }

    public function test_email_must_be_unique_when_creating_an_admin(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = User::factory()->create();

        $response = $this->post(route('page.admins.store'), [
            'name' => 'Jane Doe',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_a_super_admin_can_update_another_admins_permissions(): void
    {
        $this->actingAs(User::factory()->create());

        $admin = User::factory()->withPermissions([Permission::ManageStudents])->create();

        $response = $this->put(route('page.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'permissions' => [Permission::ScoreEntry->value],
        ]);

        $response->assertRedirect(route('page.admins.index'));

        $admin->refresh();
        $this->assertFalse($admin->hasPermission(Permission::ManageStudents));
        $this->assertTrue($admin->hasPermission(Permission::ScoreEntry));
    }

    public function test_leaving_the_password_blank_on_update_keeps_the_existing_password(): void
    {
        $this->actingAs(User::factory()->create());

        $admin = User::factory()->create();
        $originalPassword = $admin->password;

        $this->put(route('page.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
        ]);

        $this->assertSame($originalPassword, $admin->fresh()->password);
    }

    public function test_a_super_admin_cannot_delete_their_own_account(): void
    {
        $currentUser = User::factory()->create();
        $this->actingAs($currentUser);

        $response = $this->delete(route('page.admins.destroy', $currentUser));

        $response->assertStatus(422);
        $this->assertModelExists($currentUser);
    }

    public function test_a_super_admin_can_be_deleted_when_another_super_admin_remains(): void
    {
        $this->actingAs(User::factory()->create());

        $anotherSuperAdmin = User::factory()->create();

        $response = $this->delete(route('page.admins.destroy', $anotherSuperAdmin));

        $response->assertRedirect(route('page.admins.index'));
        $this->assertModelMissing($anotherSuperAdmin);
    }

    public function test_a_regular_admin_can_be_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $regularAdmin = User::factory()->withPermissions([Permission::ManageStudents])->create();

        $response = $this->delete(route('page.admins.destroy', $regularAdmin));

        $response->assertRedirect(route('page.admins.index'));
        $this->assertModelMissing($regularAdmin);
    }

    public function test_the_last_super_admin_cannot_demote_themselves(): void
    {
        $onlySuperAdmin = User::factory()->create();
        $this->actingAs($onlySuperAdmin);

        $response = $this->put(route('page.admins.update', $onlySuperAdmin), [
            'name' => $onlySuperAdmin->name,
            'email' => $onlySuperAdmin->email,
            // is_super_admin omitted entirely — matches an unchecked checkbox.
        ]);

        $response->assertStatus(422);
        $this->assertTrue($onlySuperAdmin->fresh()->is_super_admin);
    }

    public function test_a_super_admin_can_be_demoted_when_another_super_admin_remains(): void
    {
        $this->actingAs(User::factory()->create());

        $anotherSuperAdmin = User::factory()->create();

        $response = $this->put(route('page.admins.update', $anotherSuperAdmin), [
            'name' => $anotherSuperAdmin->name,
            'email' => $anotherSuperAdmin->email,
            'permissions' => [Permission::ManageStudents->value],
        ]);

        $response->assertRedirect(route('page.admins.index'));
        $this->assertFalse($anotherSuperAdmin->fresh()->is_super_admin);
    }
}
