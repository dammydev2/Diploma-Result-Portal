<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One place documenting which permission guards which route, for every
 * gated area not already covered by its own feature test file (course
 * units and score entry have their own dedicated split-permission tests).
 */
class PermissionGatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_programmes_require_the_manage_programmes_permission(): void
    {
        $this->actingAs(User::factory()->withPermissions([])->create());
        $this->get(route('programme.index'))->assertForbidden();

        $this->actingAs(User::factory()->withPermissions([Permission::ManageProgrammes])->create());
        $this->get(route('programme.index'))->assertOk();
    }

    public function test_grading_scale_requires_the_edit_grading_scale_permission(): void
    {
        $this->actingAs(User::factory()->withPermissions([])->create());
        $this->get(route('grading-scale.index'))->assertForbidden();

        $this->actingAs(User::factory()->withPermissions([Permission::EditGradingScale])->create());
        $this->get(route('grading-scale.index'))->assertOk();
    }

    public function test_diploma_classification_requires_the_edit_diploma_classification_permission(): void
    {
        $this->actingAs(User::factory()->withPermissions([])->create());
        $this->get(route('diploma-classification.index'))->assertForbidden();

        $this->actingAs(User::factory()->withPermissions([Permission::EditDiplomaClassification])->create());
        $this->get(route('diploma-classification.index'))->assertOk();
    }

    public function test_semester_result_requires_the_semester_result_permission(): void
    {
        $this->actingAs(User::factory()->withPermissions([])->create());
        $this->get(route('page.results.index'))->assertForbidden();

        $this->actingAs(User::factory()->withPermissions([Permission::SemesterResult])->create());
        $this->get(route('page.results.index'))->assertOk();
    }

    public function test_suspend_and_reactivate_require_their_own_permission_separate_from_manage_students(): void
    {
        $student = Student::factory()->create();

        // Has manage_students but not the suspend/reactivate permission.
        $this->actingAs(User::factory()->withPermissions([Permission::ManageStudents])->create());
        $this->put(route('student.suspend', $student->id))->assertForbidden();
        $this->put(route('student.reactivate', $student->id))->assertForbidden();

        $this->actingAs(User::factory()->withPermissions([Permission::SuspendReactivateStudents])->create());
        $this->put(route('student.suspend', $student->id))->assertRedirect();
        $this->put(route('student.reactivate', $student->id))->assertRedirect();
    }

    public function test_statement_of_result_is_super_admin_only_regardless_of_other_permissions(): void
    {
        $this->actingAs(User::factory()->withPermissions(Permission::cases())->create());

        $this->get(route('page.statement-of-result.index'))->assertForbidden();

        $this->actingAs(User::factory()->create());
        $this->get(route('page.statement-of-result.index'))->assertOk();
    }

    public function test_transcript_is_super_admin_only_regardless_of_other_permissions(): void
    {
        $this->actingAs(User::factory()->withPermissions(Permission::cases())->create());

        $this->get(route('page.transcript.index'))->assertForbidden();

        $this->actingAs(User::factory()->create());
        $this->get(route('page.transcript.index'))->assertOk();
    }

    public function test_institution_settings_is_super_admin_only_regardless_of_other_permissions(): void
    {
        $this->actingAs(User::factory()->withPermissions(Permission::cases())->create());

        $this->get(route('page.settings.institution'))->assertForbidden();

        $this->actingAs(User::factory()->create());
        $this->get(route('page.settings.institution'))->assertOk();
    }

    public function test_profile_and_security_settings_need_no_permission_at_all(): void
    {
        $this->actingAs(User::factory()->withPermissions([])->create());

        $this->get(route('page.settings.profile'))->assertOk();
    }
}
