<?php

namespace App\Concerns;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves the school-branding shown on printable documents: institution
 * name/address, the registrar's name/title, and the Statement of Result
 * background artwork and registrar signature image. Each of the latter two
 * falls back to a bundled default asset (shipped in public/images/
 * statement-of-result) until an admin uploads their own from the
 * Institution settings page.
 */
trait ResolvesInstitutionBranding
{
    /**
     * @return array{
     *     institutionName: string,
     *     institutionAddress: ?string,
     *     registrarName: string,
     *     registrarTitle: string,
     *     backgroundUrl: string,
     *     backgroundPath: string,
     *     signatureUrl: string,
     *     signaturePath: string,
     * }
     */
    private function resolveBranding(): array
    {
        $background = $this->resolveAsset('statement_background_path', config('institution.statement_background'));
        $signature = $this->resolveAsset('registrar_signature_path', config('institution.registrar_signature'));

        return [
            'institutionName' => Setting::get('institution_name', config('institution.name')),
            'institutionAddress' => Setting::get('institution_address', config('institution.address')),
            'registrarName' => Setting::get('registrar_name', config('institution.registrar_name')),
            'registrarTitle' => Setting::get('registrar_title', config('institution.registrar_title')),
            'backgroundUrl' => $background['url'],
            'backgroundPath' => $background['path'],
            'signatureUrl' => $signature['url'],
            'signaturePath' => $signature['path'],
        ];
    }

    /**
     * @return array{url: string, path: string}
     */
    private function resolveAsset(string $settingKey, string $defaultPublicPath): array
    {
        $uploadedPath = Setting::get($settingKey);

        if ($uploadedPath) {
            return [
                'url' => Storage::disk('public')->url($uploadedPath),
                'path' => Storage::disk('public')->path($uploadedPath),
            ];
        }

        return [
            'url' => asset($defaultPublicPath),
            'path' => public_path($defaultPublicPath),
        ];
    }
}
