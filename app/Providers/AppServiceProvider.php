<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configurePermissionGates();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * One gate per {@see Permission} case (named after its value, so routes
     * can reference it directly as `can:manage_programmes` etc.), plus a
     * handful of compound gates for pages that accept more than one
     * permission, and a super-admin-only gate for pages no permission can
     * grant access to (Statement of Result, Institution settings).
     */
    protected function configurePermissionGates(): void
    {
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user): bool => $user->hasPermission($permission));
        }

        Gate::define(
            'view_course_units',
            fn (User $user): bool => $user->hasPermission(Permission::AddCourseUnits)
                || $user->hasPermission(Permission::EditCourseUnits)
        );

        Gate::define(
            'view_scores',
            fn (User $user): bool => $user->hasPermission(Permission::ScoreEntry)
                || $user->hasPermission(Permission::EditScore)
        );

        Gate::define('super_admin_only', fn (User $user): bool => $user->is_super_admin);
    }
}
