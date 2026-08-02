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
    Route::crud('programme', 'ProgrammeCrudController');
    Route::crud('course-unit', 'CourseUnitCrudController');
    Route::get('courses', 'CoursesController@index')->name('page.courses.index');

    Route::get('settings', function () {
        return redirect()->route('page.settings.profile');
    })->name('page.settings.index');

    Route::get('settings/profile', 'ProfileController@edit')->name('page.settings.profile');
    Route::put('settings/profile', 'ProfileController@update')->name('page.settings.profile.update');
    Route::delete('settings/profile', 'ProfileController@destroy')->name('page.settings.profile.destroy');

    Route::middleware('password.confirm')->group(function () {
        Route::get('settings/security', 'SecurityController@edit')->name('page.settings.security');
        Route::put('settings/security/password', 'SecurityController@updatePassword')->name('page.settings.security.password');
        Route::post('settings/security/two-factor', 'SecurityController@enableTwoFactor')->name('page.settings.security.two-factor.enable');
        Route::post('settings/security/two-factor/confirm', 'SecurityController@confirmTwoFactor')->name('page.settings.security.two-factor.confirm');
        Route::delete('settings/security/two-factor', 'SecurityController@disableTwoFactor')->name('page.settings.security.two-factor.disable');
        Route::post('settings/security/two-factor/recovery-codes', 'SecurityController@regenerateRecoveryCodes')->name('page.settings.security.recovery-codes');
    });
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
