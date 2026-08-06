<?php

namespace App\Concerns;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves the school-branding shown on printable documents: institution
 * name/address, the registrar's name/title, and the background artwork and
 * registrar signature image used on the Statement of Result and Transcript.
 * Each background/signature falls back to a bundled default asset (shipped
 * in public/images/statement-of-result and public/images/transcript) until
 * an admin uploads their own from the Institution settings page.
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
     *     transcriptBackgroundUrl: string,
     *     transcriptBackgroundPath: string,
     * }
     */
    private function resolveBranding(): array
    {
        $background = $this->resolveAsset('statement_background_path', config('institution.statement_background'));
        $signature = $this->resolveAsset('registrar_signature_path', config('institution.registrar_signature'));
        $transcriptBackground = $this->resolveAsset('transcript_background_path', config('institution.transcript_background'));

        return [
            'institutionName' => Setting::get('institution_name', config('institution.name')),
            'institutionAddress' => Setting::get('institution_address', config('institution.address')),
            'registrarName' => Setting::get('registrar_name', config('institution.registrar_name')),
            'registrarTitle' => Setting::get('registrar_title', config('institution.registrar_title')),
            'backgroundUrl' => $background['url'],
            'backgroundPath' => $background['path'],
            'signatureUrl' => $signature['url'],
            'signaturePath' => $signature['path'],
            'transcriptBackgroundUrl' => $transcriptBackground['url'],
            'transcriptBackgroundPath' => $transcriptBackground['path'],
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
