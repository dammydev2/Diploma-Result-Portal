<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\ProfileValidationRules;
use App\Enums\Permission;
use App\Models\User;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/**
 * Class AdminUserController
 *
 * @property-read CrudPanel $crud
 */
class AdminUserController extends Controller
{
    use ProfileValidationRules;

    public function index(): View
    {
        return view('admin.admins.index', [
            'title' => 'Admins',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Admins' => false,
            ],
            'admins' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.form', [
            'title' => 'Add admin',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Admins' => route('page.admins.index'),
                'Add admin' => false,
            ],
            'admin' => null,
            'permissions' => Permission::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->adminRules());

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_super_admin' => $request->boolean('is_super_admin'),
            'permissions' => $this->permissionsFromRequest($request),
        ]);

        \Alert::success('Admin added.')->flash();

        return redirect()->route('page.admins.index');
    }

    public function edit(User $admin): View
    {
        return view('admin.admins.form', [
            'title' => 'Edit admin',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Admins' => route('page.admins.index'),
                'Edit admin' => false,
            ],
            'admin' => $admin,
            'permissions' => Permission::cases(),
        ]);
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        $validated = $request->validate($this->adminRules($admin->id, passwordRequired: false));

        $wasOnlySuperAdmin = $admin->is_super_admin && User::query()->where('is_super_admin', true)->count() === 1;

        abort_if(
            $wasOnlySuperAdmin && ! $request->boolean('is_super_admin'),
            422,
            'At least one super admin must remain.'
        );

        $admin->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_super_admin' => $request->boolean('is_super_admin'),
            'permissions' => $this->permissionsFromRequest($request),
        ]);

        if (! empty($validated['password'])) {
            $admin->password = $validated['password'];
        }

        $admin->save();

        \Alert::success('Admin updated.')->flash();

        return redirect()->route('page.admins.index');
    }

    public function destroy(User $admin): RedirectResponse
    {
        abort_if($admin->is($this->currentUser()), 422, 'You cannot delete your own account.');

        $admin->delete();

        \Alert::success('Admin removed.')->flash();

        return redirect()->route('page.admins.index');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function adminRules(?int $userId = null, bool $passwordRequired = true): array
    {
        return [
            ...$this->profileRules($userId),
            'password' => $passwordRequired
                ? ['required', 'string', Password::default(), 'confirmed']
                : ['nullable', 'string', Password::default(), 'confirmed'],
        ];
    }

    /**
     * @return list<Permission>
     */
    private function permissionsFromRequest(Request $request): array
    {
        /** @var list<string> $selected */
        $selected = $request->input('permissions', []);

        return array_values(array_filter(array_map(
            fn (string $value): ?Permission => Permission::tryFrom($value),
            $selected
        )));
    }

    private function currentUser(): User
    {
        return Auth::user();
    }
}
