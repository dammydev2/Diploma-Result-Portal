<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\PasswordValidationRules;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;

/**
 * Class SecurityController
 *
 * @property-read CrudPanel $crud
 */
class SecurityController extends Controller
{
    use PasswordValidationRules;

    public function edit(): View
    {
        $user = Auth::user();

        $canManageTwoFactor = Features::canManageTwoFactorAuthentication();
        $twoFactorEnabled = $canManageTwoFactor && $user->hasEnabledTwoFactorAuthentication();
        $twoFactorPending = $canManageTwoFactor && ! $twoFactorEnabled && ! empty($user->two_factor_secret);

        $qrCodeSvg = null;
        $manualSetupKey = null;
        $recoveryCodes = [];

        if ($twoFactorPending) {
            $qrCodeSvg = $user->twoFactorQrCodeSvg();
            $manualSetupKey = decrypt($user->two_factor_secret);
        }

        if ($twoFactorEnabled && $user->two_factor_recovery_codes) {
            $recoveryCodes = $user->recoveryCodes();
        }

        $canManagePasskeys = Features::canManagePasskeys();
        $passkeys = $canManagePasskeys
            ? $user->passkeys()->latest()->get()
            : collect();

        return view('admin.settings.security', [
            'title' => 'Security settings',
            'breadcrumbs' => [
                trans('backpack::crud.admin') => backpack_url('dashboard'),
                'Settings' => false,
                'Security' => false,
            ],
            'canManageTwoFactor' => $canManageTwoFactor,
            'twoFactorEnabled' => $twoFactorEnabled,
            'twoFactorPending' => $twoFactorPending,
            'qrCodeSvg' => $qrCodeSvg,
            'manualSetupKey' => $manualSetupKey,
            'recoveryCodes' => $recoveryCodes,
            'canManagePasskeys' => $canManagePasskeys,
            'passkeys' => $passkeys,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ]);

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        \Alert::success('Password updated.')->flash();

        return redirect()->route('page.settings.security');
    }

    public function enableTwoFactor(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): RedirectResponse
    {
        $enableTwoFactorAuthentication(Auth::user());

        return redirect()->route('page.settings.security');
    }

    public function confirmTwoFactor(Request $request, ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $confirmTwoFactorAuthentication(Auth::user(), $request->string('code'));

        \Alert::success('Two-factor authentication enabled.')->flash();

        return redirect()->route('page.settings.security');
    }

    public function disableTwoFactor(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): RedirectResponse
    {
        $disableTwoFactorAuthentication(Auth::user());

        \Alert::success('Two-factor authentication disabled.')->flash();

        return redirect()->route('page.settings.security');
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): RedirectResponse
    {
        $generateNewRecoveryCodes(Auth::user());

        \Alert::success('Recovery codes regenerated.')->flash();

        return redirect()->route('page.settings.security');
    }
}
