<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class InstitutionSettingsController
 *
 * @property-read CrudPanel $crud
 */
class InstitutionSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.institution', [
            'title' => 'Institution settings',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Settings' => false,
                'Institution' => false,
            ],
            'name' => Setting::get('institution_name', config('institution.name')),
            'address' => Setting::get('institution_address', config('institution.address')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('institution_name', $validated['name']);
        Setting::set('institution_address', $validated['address'] ?? '');

        \Alert::success('Institution settings updated.')->flash();

        return redirect()->route('page.settings.institution');
    }
}
