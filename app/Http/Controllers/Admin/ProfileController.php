<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Livewire\Actions\Logout;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProfileController
 *
 * @property-read CrudPanel $crud
 */
class ProfileController extends Controller
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public function edit(): View
    {
        return view('admin.settings.profile', [
            'title' => 'Profile settings',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Settings' => false,
                'Profile' => false,
            ],
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate($this->profileRules($user->id));

        $user->fill($validated)->save();

        \Alert::success('Profile updated.')->flash();

        return redirect()->route('page.settings.profile');
    }

    public function destroy(Request $request, Logout $logout): RedirectResponse
    {
        $request->validateWithBag('deleteAccount', [
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        return redirect('/');
    }
}
