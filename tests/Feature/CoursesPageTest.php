<?php

namespace Tests\Feature;

use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.courses.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_courses_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.courses.index'));

        $response->assertOk();
    }

    public function test_courses_are_listed_for_the_selected_programme_year_and_semester(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        $matching = CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'year' => 1,
            'semester' => 1,
        ]);

        $otherSemester = CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'year' => 1,
            'semester' => 2,
        ]);

        $response = $this->get(route('page.courses.index', [
            'programme_id' => $programme->id,
            'year' => 1,
            'semester' => 1,
        ]));

        $response->assertOk();
        $response->assertSee($matching->code);
        $response->assertDontSee($otherSemester->code);
    }

    public function test_no_courses_are_shown_until_a_full_selection_is_made(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'year' => 1,
            'semester' => 1,
        ]);

        $response = $this->get(route('page.courses.index', ['programme_id' => $programme->id]));

        $response->assertOk();
        $response->assertSee('Select a programme, year and semester to view courses.');
    }
}
