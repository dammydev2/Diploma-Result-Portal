<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\ResolvesInstitutionBranding;
use App\Models\Setting;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * Class InstitutionSettingsController
 *
 * @property-read CrudPanel $crud
 */
class InstitutionSettingsController extends Controller
{
    use ResolvesInstitutionBranding;

    public function edit(): View
    {
        $branding = $this->resolveBranding();

        return view('admin.settings.institution', [
            'title' => 'Institution settings',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Settings' => false,
                'Institution' => false,
            ],
            'name' => $branding['institutionName'],
            'address' => $branding['institutionAddress'],
            'registrarName' => $branding['registrarName'],
            'registrarTitle' => $branding['registrarTitle'],
            'backgroundUrl' => $branding['backgroundUrl'],
            'signatureUrl' => $branding['signatureUrl'],
            'transcriptBackgroundUrl' => $branding['transcriptBackgroundUrl'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'registrar_name' => ['required', 'string', 'max:255'],
            'registrar_title' => ['required', 'string', 'max:255'],
            'statement_background' => ['nullable', 'image', 'max:5120'],
            'registrar_signature' => ['nullable', 'image', 'max:5120'],
            'transcript_background' => ['nullable', 'image', 'max:5120'],
        ]);

        Setting::set('institution_name', $validated['name']);
        Setting::set('institution_address', $validated['address'] ?? '');
        Setting::set('registrar_name', $validated['registrar_name']);
        Setting::set('registrar_title', $validated['registrar_title']);

        $this->storeUploadIfPresent($request, 'statement_background', 'statement_background_path', 'statement-background');
        $this->storeUploadIfPresent($request, 'registrar_signature', 'registrar_signature_path', 'registrar-signature');
        $this->storeUploadIfPresent($request, 'transcript_background', 'transcript_background_path', 'transcript-background');

        \Alert::success('Institution settings updated.')->flash();

        return redirect()->route('page.settings.institution');
    }

    /**
     * Store an uploaded file under storage/app/public/institution, replacing
     * whatever was previously stored for this setting key, and no-op if no
     * file was submitted for this input.
     */
    private function storeUploadIfPresent(Request $request, string $inputName, string $settingKey, string $filenameBase): void
    {
        if (! $request->hasFile($inputName)) {
            return;
        }

        /** @var UploadedFile $file */
        $file = $request->file($inputName);

        $previousPath = Setting::get($settingKey);
        if ($previousPath) {
            Storage::disk('public')->delete($previousPath);
        }

        $path = $file->storeAs('institution', $filenameBase.'.'.$file->extension(), 'public');

        abort_if($path === false, 500, 'Failed to store the uploaded file.');

        Setting::set($settingKey, $path);
    }
}
