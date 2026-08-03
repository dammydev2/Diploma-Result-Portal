<?php

namespace Tests\Feature;

use App\Models\DiplomaClassification;
use App\Models\User;
use Database\Seeders\DiplomaClassificationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiplomaClassificationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('diploma-classification.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_diploma_classification_list(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(DiplomaClassificationSeeder::class);

        $response = $this->get(route('diploma-classification.index'));

        $response->assertOk();
    }

    public function test_the_list_data_includes_every_seeded_band(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(DiplomaClassificationSeeder::class);

        $response = $this->post(route('diploma-classification.index').'/search');

        $response->assertOk();
        $this->assertStringContainsString('Distinction', $response->getContent());
        $this->assertStringContainsString('Fail', $response->getContent());
    }

    public function test_a_band_with_a_zero_min_cgpa_still_displays_it(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(DiplomaClassificationSeeder::class);

        $response = $this->post(route('diploma-classification.index').'/search');

        $response->assertOk();
        // The Fail band has min_cgpa = 0.00.
        $this->assertStringNotContainsString('>-<', $response->getContent());
    }

    public function test_there_is_no_create_route(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('diploma-classification.index').'/create');

        $response->assertMethodNotAllowed();
    }

    public function test_there_is_no_delete_route(): void
    {
        $this->actingAs(User::factory()->create());

        $diplomaClassification = DiplomaClassification::factory()->create();

        $response = $this->delete(route('diploma-classification.index').'/'.$diplomaClassification->id);

        $response->assertMethodNotAllowed();

        $this->assertModelExists($diplomaClassification);
    }

    public function test_a_diploma_classification_band_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $diplomaClassification = DiplomaClassification::factory()->create([
            'classification' => 'Distinction',
            'min_cgpa' => 3.50,
            'max_cgpa' => 4.00,
        ]);

        $response = $this->put(route('diploma-classification.update', $diplomaClassification->id), [
            'id' => $diplomaClassification->id,
            'classification' => 'Distinction',
            'min_cgpa' => 3.60,
            'max_cgpa' => 4.00,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('diploma_classifications', [
            'id' => $diplomaClassification->id,
            'min_cgpa' => 3.60,
        ]);
    }

    public function test_min_cgpa_cannot_be_greater_than_max_cgpa(): void
    {
        $this->actingAs(User::factory()->create());

        $diplomaClassification = DiplomaClassification::factory()->create();

        $response = $this->put(route('diploma-classification.update', $diplomaClassification->id), [
            'id' => $diplomaClassification->id,
            'classification' => $diplomaClassification->classification,
            'min_cgpa' => 3.00,
            'max_cgpa' => 2.00,
        ]);

        $response->assertSessionHasErrors(['min_cgpa', 'max_cgpa']);
    }
}
