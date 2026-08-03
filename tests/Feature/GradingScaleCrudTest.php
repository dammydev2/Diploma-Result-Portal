<?php

namespace Tests\Feature;

use App\Models\GradingScale;
use App\Models\User;
use Database\Seeders\GradingScaleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingScaleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('grading-scale.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_grading_scale_list(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $response = $this->get(route('grading-scale.index'));

        $response->assertOk();
    }

    public function test_the_list_data_includes_every_seeded_band(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $response = $this->post(route('grading-scale.index').'/search');

        $response->assertOk();
        $this->assertStringContainsString('4.00', $response->getContent());
        $this->assertStringContainsString('0.00', $response->getContent());
    }

    public function test_a_band_with_a_zero_min_score_still_displays_it(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(GradingScaleSeeder::class);

        $response = $this->post(route('grading-scale.index').'/search');

        $response->assertOk();
        // The F band has min_score = 0; Backpack's default text column
        // treats 0 as "empty" and would otherwise render a "-" placeholder.
        $this->assertStringNotContainsString('>-<', $response->getContent());
    }

    public function test_there_is_no_create_route(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('grading-scale.index').'/create');

        $response->assertMethodNotAllowed();
    }

    public function test_there_is_no_delete_route(): void
    {
        $this->actingAs(User::factory()->create());

        $gradingScale = GradingScale::factory()->create();

        $response = $this->delete(route('grading-scale.index').'/'.$gradingScale->id);

        $response->assertMethodNotAllowed();

        $this->assertModelExists($gradingScale);
    }

    public function test_a_grading_scale_band_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $gradingScale = GradingScale::factory()->create([
            'letter_grade' => 'A',
            'min_score' => 75,
            'max_score' => 100,
            'weighting' => 4.00,
        ]);

        $response = $this->put(route('grading-scale.update', $gradingScale->id), [
            'id' => $gradingScale->id,
            'letter_grade' => 'A',
            'min_score' => 70,
            'max_score' => 100,
            'weighting' => 4.50,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('grading_scales', [
            'id' => $gradingScale->id,
            'min_score' => 70,
            'weighting' => 4.50,
        ]);
    }

    public function test_min_score_cannot_be_greater_than_max_score(): void
    {
        $this->actingAs(User::factory()->create());

        $gradingScale = GradingScale::factory()->create();

        $response = $this->put(route('grading-scale.update', $gradingScale->id), [
            'id' => $gradingScale->id,
            'letter_grade' => $gradingScale->letter_grade,
            'min_score' => 50,
            'max_score' => 40,
            'weighting' => $gradingScale->weighting,
        ]);

        $response->assertSessionHasErrors(['min_score', 'max_score']);
    }
}
