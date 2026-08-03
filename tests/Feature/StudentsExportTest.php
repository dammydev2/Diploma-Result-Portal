<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page_for_pdf_export(): void
    {
        $response = $this->get(route('page.students.export.pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_to_the_login_page_for_excel_export(): void
    {
        $response = $this->get(route('page.students.export.excel'));

        $response->assertRedirect(route('login'));
    }

    public function test_pdf_export_is_not_found_without_a_full_filter_selection(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.students.export.pdf'));

        $response->assertNotFound();
    }

    public function test_excel_export_is_not_found_without_a_full_filter_selection(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.students.export.excel'));

        $response->assertNotFound();
    }

    public function test_pdf_export_downloads_the_filtered_students(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        Student::factory()->create([
            'programme_id' => $programme->id,
            'matric_no' => '2024/0001',
            'year_of_admission' => 2024,
        ]);

        $response = $this->get(route('page.students.export.pdf', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_excel_export_downloads_the_filtered_students(): void
    {
        $this->actingAs(User::factory()->create());

        $programme = Programme::factory()->create();

        Student::factory()->create([
            'programme_id' => $programme->id,
            'matric_no' => '2024/0001',
            'year_of_admission' => 2024,
        ]);

        $response = $this->get(route('page.students.export.excel', [
            'programme_id' => $programme->id,
            'year_of_admission' => 2024,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }
}
