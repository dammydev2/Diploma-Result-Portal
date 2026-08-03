<?php

namespace Tests\Feature;

use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursesExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page_for_pdf_export(): void
    {
        $response = $this->get(route('page.courses.export.pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_to_the_login_page_for_excel_export(): void
    {
        $response = $this->get(route('page.courses.export.excel'));

        $response->assertRedirect(route('login'));
    }

    public function test_pdf_export_is_not_found_without_a_full_filter_selection(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.courses.export.pdf'));

        $response->assertNotFound();
    }

    public function test_excel_export_is_not_found_without_a_full_filter_selection(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.courses.export.excel'));

        $response->assertNotFound();
    }

    public function test_pdf_export_downloads_the_filtered_course_units(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'code' => 'COM 111',
            'year' => 1,
            'semester' => 1,
            'credit_units' => 3,
        ]);

        $response = $this->get(route('page.courses.export.pdf', [
            'programme_id' => $programme->id,
            'year' => 1,
            'semester' => 1,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_excel_export_downloads_the_filtered_course_units(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        CourseUnit::factory()->create([
            'programme_id' => $programme->id,
            'code' => 'COM 111',
            'year' => 1,
            'semester' => 1,
            'credit_units' => 3,
        ]);

        $response = $this->get(route('page.courses.export.excel', [
            'programme_id' => $programme->id,
            'year' => 1,
            'semester' => 1,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }
}
