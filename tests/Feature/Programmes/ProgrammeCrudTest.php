<?php

namespace Tests\Feature\Programmes;

use App\Enums\ProgrammeLevel;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('programme.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_programmes_list(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('programme.index'));

        $response->assertOk();
    }

    public function test_authenticated_users_can_view_a_single_programme(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $response = $this->get(route('programme.show', $programme->id));

        $response->assertOk();
    }

    public function test_a_programme_can_be_created(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('programme.store'), [
            'name' => 'Computer Science',
            'code' => 'COM-ND',
            'level' => ProgrammeLevel::NationalDiploma->value,
            'min_credit_units' => 72,
            'max_credit_units' => 80,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('programmes', [
            'name' => 'Computer Science',
            'code' => 'COM-ND',
            'level' => ProgrammeLevel::NationalDiploma->value,
            'min_credit_units' => 72,
            'max_credit_units' => 80,
        ]);
    }

    public function test_programme_name_code_and_level_are_required(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('programme.store'), [
            'name' => '',
            'code' => '',
            'level' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'code', 'level']);
    }

    public function test_programme_code_must_be_unique(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = Programme::factory()->create();

        $response = $this->post(route('programme.store'), [
            'name' => 'A Different Name',
            'code' => $existing->code,
            'level' => $existing->level->value,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_programme_name_must_be_unique_within_the_same_level(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = Programme::factory()->create(['level' => ProgrammeLevel::NationalDiploma]);

        $response = $this->post(route('programme.store'), [
            'name' => $existing->name,
            'code' => 'DIFFERENT-CODE',
            'level' => ProgrammeLevel::NationalDiploma->value,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_the_same_programme_name_can_be_reused_across_different_levels(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = Programme::factory()->create(['name' => 'Computer Science', 'level' => ProgrammeLevel::NationalDiploma]);

        $response = $this->post(route('programme.store'), [
            'name' => $existing->name,
            'code' => 'COM-HND',
            'level' => ProgrammeLevel::HigherNationalDiploma->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('programmes', [
            'name' => 'Computer Science',
            'level' => ProgrammeLevel::HigherNationalDiploma->value,
        ]);
    }

    public function test_a_programme_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $response = $this->put(route('programme.update', $programme->id), [
            'id' => $programme->id,
            'name' => 'Updated Programme Name',
            'code' => 'UPD-001',
            'level' => $programme->level->value,
            'min_credit_units' => $programme->min_credit_units,
            'max_credit_units' => $programme->max_credit_units,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('programmes', [
            'id' => $programme->id,
            'name' => 'Updated Programme Name',
            'code' => 'UPD-001',
        ]);
    }

    public function test_a_programme_can_be_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $response = $this->delete(route('programme.destroy', $programme->id));

        $response->assertOk();

        $this->assertDatabaseMissing('programmes', ['id' => $programme->id]);
    }
}
