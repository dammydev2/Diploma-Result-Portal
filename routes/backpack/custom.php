<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::middleware('can:manage_programmes')->group(function () {
        Route::crud('programme', 'ProgrammeCrudController');
    });

    Route::middleware('can:view_course_units')->group(function () {
        Route::crud('course-unit', 'CourseUnitCrudController');
        Route::get('courses', 'CoursesController@index')->name('page.courses.index');
        Route::get('courses/export/pdf', 'CoursesController@exportPdf')->name('page.courses.export.pdf');
        Route::get('courses/export/excel', 'CoursesController@exportExcel')->name('page.courses.export.excel');
    });

    // Settings: Profile and Security are personal to whoever's logged in —
    // no permission required. Institution branding is super-admin only.
    Route::get('settings', function () {
        return redirect()->route('page.settings.profile');
    })->name('page.settings.index');

    Route::get('settings/profile', 'ProfileController@edit')->name('page.settings.profile');
    Route::put('settings/profile', 'ProfileController@update')->name('page.settings.profile.update');
    Route::delete('settings/profile', 'ProfileController@destroy')->name('page.settings.profile.destroy');

    Route::middleware('can:super_admin_only')->group(function () {
        Route::get('settings/institution', 'InstitutionSettingsController@edit')->name('page.settings.institution');
        Route::put('settings/institution', 'InstitutionSettingsController@update')->name('page.settings.institution.update');

        Route::get('admins', 'AdminUserController@index')->name('page.admins.index');
        Route::get('admins/create', 'AdminUserController@create')->name('page.admins.create');
        Route::post('admins', 'AdminUserController@store')->name('page.admins.store');
        Route::get('admins/{admin}/edit', 'AdminUserController@edit')->name('page.admins.edit');
        Route::put('admins/{admin}', 'AdminUserController@update')->name('page.admins.update');
        Route::delete('admins/{admin}', 'AdminUserController@destroy')->name('page.admins.destroy');
    });

    Route::middleware('password.confirm')->group(function () {
        Route::get('settings/security', 'SecurityController@edit')->name('page.settings.security');
        Route::put('settings/security/password', 'SecurityController@updatePassword')->name('page.settings.security.password');
        Route::post('settings/security/two-factor', 'SecurityController@enableTwoFactor')->name('page.settings.security.two-factor.enable');
        Route::post('settings/security/two-factor/confirm', 'SecurityController@confirmTwoFactor')->name('page.settings.security.two-factor.confirm');
        Route::delete('settings/security/two-factor', 'SecurityController@disableTwoFactor')->name('page.settings.security.two-factor.disable');
        Route::post('settings/security/two-factor/recovery-codes', 'SecurityController@regenerateRecoveryCodes')->name('page.settings.security.recovery-codes');
    });

    Route::middleware('can:manage_students')->group(function () {
        Route::crud('student', 'StudentCrudController');
        Route::get('students', 'StudentsController@index')->name('page.students.index');
        Route::get('students/export/pdf', 'StudentsController@exportPdf')->name('page.students.export.pdf');
        Route::get('students/export/excel', 'StudentsController@exportExcel')->name('page.students.export.excel');
    });

    Route::middleware('can:suspend_reactivate_students')->group(function () {
        Route::put('student/{id}/suspend', 'StudentCrudController@suspend')->name('student.suspend');
        Route::put('student/{id}/reactivate', 'StudentCrudController@reactivate')->name('student.reactivate');
    });

    Route::middleware('can:edit_grading_scale')->group(function () {
        Route::crud('grading-scale', 'GradingScaleCrudController');
    });

    Route::middleware('can:edit_diploma_classification')->group(function () {
        Route::crud('diploma-classification', 'DiplomaClassificationCrudController');
    });

    Route::middleware('can:view_scores')->group(function () {
        Route::get('scores', 'ScoresController@index')->name('page.scores.index');
        Route::post('scores', 'ScoresController@store')->name('page.scores.store');
    });

    Route::middleware('can:semester_result')->group(function () {
        Route::get('results', 'ResultsController@index')->name('page.results.index');
        Route::get('results/export/pdf', 'ResultsController@exportPdf')->name('page.results.export.pdf');
        Route::get('results/export/excel', 'ResultsController@exportExcel')->name('page.results.export.excel');
    });

    Route::middleware('can:super_admin_only')->group(function () {
        Route::get('statement-of-result', 'StatementOfResultController@index')->name('page.statement-of-result.index');
        Route::get('statement-of-result/export/pdf', 'StatementOfResultController@exportPdf')->name('page.statement-of-result.export.pdf');

        Route::get('transcript', 'TranscriptController@index')->name('page.transcript.index');
        Route::get('transcript/export/pdf', 'TranscriptController@exportPdf')->name('page.transcript.export.pdf');
    });
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
