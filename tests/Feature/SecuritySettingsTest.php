<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsConfirmed(): User
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        session(['auth.password_confirmed_at' => time()]);

        return $user;
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('page.settings.security'));

        $response->assertRedirect(route('login'));
    }

    public function test_unconfirmed_sessions_are_redirected_to_confirm_password(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('page.settings.security'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_authenticated_confirmed_users_can_visit_the_security_page(): void
    {
        $this->actingAsConfirmed();

        $response = $this->get(route('page.settings.security'));

        $response->assertOk();
    }

    public function test_the_password_can_be_updated(): void
    {
        $user = $this->actingAsConfirmed();

        $response = $this->put(route('page.settings.security.password'), [
            'current_password' => 'password',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ]);

        $response->assertRedirect(route('page.settings.security'));

        $this->assertTrue(\Hash::check('new-strong-password', $user->fresh()->password));
    }

    public function test_the_password_update_requires_the_correct_current_password(): void
    {
        $this->actingAsConfirmed();

        $response = $this->put(route('page.settings.security.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_two_factor_authentication_can_be_enabled_and_confirmed(): void
    {
        $user = $this->actingAsConfirmed();

        $this->post(route('page.settings.security.two-factor.enable'))
            ->assertRedirect(route('page.settings.security'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);

        $response = $this->get(route('page.settings.security'));
        $response->assertOk();
        $response->assertSee('Confirmation code');

        $code = app(Google2FA::class)
            ->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));

        $this->post(route('page.settings.security.two-factor.confirm'), ['code' => $code])
            ->assertRedirect(route('page.settings.security'));

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->assertTrue($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_two_factor_confirmation_fails_with_an_invalid_code(): void
    {
        $user = $this->actingAsConfirmed();

        $this->post(route('page.settings.security.two-factor.enable'));

        $response = $this->post(route('page.settings.security.two-factor.confirm'), ['code' => '000000']);

        $response->assertSessionHasErrorsIn('confirmTwoFactorAuthentication', ['code']);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_two_factor_authentication_can_be_disabled(): void
    {
        $user = $this->actingAsConfirmed();

        app(EnableTwoFactorAuthentication::class)($user);
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        $response = $this->delete(route('page.settings.security.two-factor.disable'));

        $response->assertRedirect(route('page.settings.security'));
        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_recovery_codes_can_be_regenerated(): void
    {
        $user = $this->actingAsConfirmed();

        app(EnableTwoFactorAuthentication::class)($user);
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        $originalCodes = $user->fresh()->recoveryCodes();

        $response = $this->post(route('page.settings.security.recovery-codes'));

        $response->assertRedirect(route('page.settings.security'));
        $this->assertNotEquals($originalCodes, $user->fresh()->recoveryCodes());
    }
}
