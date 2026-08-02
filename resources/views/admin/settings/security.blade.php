@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Security settings</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Manage your password, two-factor authentication and passkeys</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    @include('admin.settings.partials.nav')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Update password</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Ensure your account is using a long, random password to stay secure</p>

                    <form method="POST" action="{{ route('page.settings.security.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="current_password">Current password</label>
                            <input
                                type="password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                id="current_password"
                                name="current_password"
                                autocomplete="current-password"
                            >
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">New password</label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                autocomplete="new-password"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Confirm password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                autocomplete="new-password"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>

            @if ($canManageTwoFactor)
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Two-factor authentication</h3>
                    </div>
                    <div class="card-body">
                        @if ($twoFactorEnabled)
                            <p class="text-muted">
                                You will be prompted for a secure, random pin during login, which you can retrieve
                                from the TOTP-supported application on your phone.
                            </p>

                            <form method="POST" action="{{ route('page.settings.security.two-factor.disable') }}" class="mb-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Disable 2FA</button>
                            </form>

                            <div class="border rounded p-3">
                                <h4>2FA recovery codes</h4>
                                <p class="text-muted">
                                    Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.
                                </p>

                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#recoveryCodes">
                                    View / hide recovery codes
                                </button>

                                <div class="collapse mt-3" id="recoveryCodes">
                                    @if (filled($recoveryCodes))
                                        <div class="bg-body-tertiary rounded p-3 font-monospace">
                                            @foreach ($recoveryCodes as $code)
                                                <div>{{ $code }}</div>
                                            @endforeach
                                        </div>
                                        <p class="text-muted small mt-2 mb-0">
                                            Each recovery code can be used once to access your account and will be removed after use.
                                            If you need more, regenerate below.
                                        </p>

                                        <form method="POST" action="{{ route('page.settings.security.recovery-codes') }}" class="mt-3">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">Regenerate codes</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @elseif ($twoFactorPending)
                            <p class="text-muted">
                                Scan the QR code below with your authenticator app, or enter the setup key manually,
                                then confirm the 6-digit code it generates.
                            </p>

                            <div class="d-flex justify-content-center mb-3">
                                <div class="bg-white p-3 rounded d-inline-block">
                                    {!! $qrCodeSvg !!}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Setup key</label>
                                <input type="text" class="form-control" readonly value="{{ $manualSetupKey }}">
                            </div>

                            <form method="POST" action="{{ route('page.settings.security.two-factor.confirm') }}" class="mb-3">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="code">Confirmation code</label>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        maxlength="6"
                                    >
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Confirm</button>
                            </form>

                            <form method="POST" action="{{ route('page.settings.security.two-factor.disable') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link text-muted p-0">Cancel setup</button>
                            </form>
                        @else
                            <p class="text-muted">
                                When you enable two-factor authentication, you will be prompted for a secure pin during
                                login. This pin can be retrieved from a TOTP-supported application on your phone.
                            </p>

                            <form method="POST" action="{{ route('page.settings.security.two-factor.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Enable 2FA</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if ($canManagePasskeys)
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Passkeys</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage your passkeys for passwordless sign-in</p>

                        @forelse ($passkeys as $passkey)
                            <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <div>
                                    <div class="fw-medium">
                                        {{ $passkey->name }}
                                        @if ($passkey->authenticator)
                                            <span class="badge bg-secondary-lt ms-1">{{ $passkey->authenticator }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        Added {{ $passkey->created_at->diffForHumans() }}
                                        @if ($passkey->last_used_at)
                                            &middot; Last used {{ $passkey->last_used_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('passkey.destroy', $passkey) }}" onsubmit="return confirm('Remove passkey \'{{ $passkey->name }}\'? You will no longer be able to use it to sign in.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-muted">No passkeys yet.</p>
                        @endforelse

                        <div id="passkey-unsupported" class="text-muted mt-3" style="display: none;">
                            Passkeys are not supported in this browser.
                        </div>

                        <div id="passkey-add-trigger" class="mt-3" style="display: none;">
                            <button type="button" class="btn btn-primary" id="passkey-add-button">Add passkey</button>
                        </div>

                        <div id="passkey-add-form" class="mt-3" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label" for="passkey-name">Passkey name</label>
                                <input type="text" class="form-control" id="passkey-name" placeholder="e.g., MacBook Pro, iPhone">
                                <div class="form-text">Give this passkey a name to help you identify it later.</div>
                            </div>
                            <p id="passkey-error" class="text-danger small" style="display: none;"></p>
                            <button type="button" class="btn btn-primary" id="passkey-register-button">Register passkey</button>
                            <button type="button" class="btn btn-link" id="passkey-cancel-button">Cancel</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

@if ($canManagePasskeys)
    @push('after_scripts')
        @vite('resources/js/passkeys.js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var unsupported = document.getElementById('passkey-unsupported');
                var trigger = document.getElementById('passkey-add-trigger');
                var form = document.getElementById('passkey-add-form');
                var addButton = document.getElementById('passkey-add-button');
                var registerButton = document.getElementById('passkey-register-button');
                var cancelButton = document.getElementById('passkey-cancel-button');
                var nameInput = document.getElementById('passkey-name');
                var errorText = document.getElementById('passkey-error');

                function updateSupport() {
                    var supported = Boolean(window.Passkeys && window.Passkeys.isSupported());
                    unsupported.style.display = supported ? 'none' : 'block';
                    trigger.style.display = supported ? 'block' : 'none';
                }

                updateSupport();
                window.addEventListener('passkeys:ready', updateSupport, { once: true });

                addButton.addEventListener('click', function () {
                    trigger.style.display = 'none';
                    form.style.display = 'block';
                    nameInput.focus();
                });

                cancelButton.addEventListener('click', function () {
                    form.style.display = 'none';
                    trigger.style.display = 'block';
                    nameInput.value = '';
                    errorText.style.display = 'none';
                });

                registerButton.addEventListener('click', function () {
                    var name = nameInput.value.trim();
                    if (! name) {
                        return;
                    }

                    errorText.style.display = 'none';
                    registerButton.disabled = true;
                    registerButton.textContent = 'Registering...';

                    window.Passkeys.register({ name: name }).then(function () {
                        window.location.reload();
                    }).catch(function (e) {
                        registerButton.disabled = false;
                        registerButton.textContent = 'Register passkey';

                        if (e && e.constructor && e.constructor.name === 'UserCancelledError') {
                            return;
                        }

                        errorText.textContent = (e && e.message) || 'Something went wrong registering this passkey.';
                        errorText.style.display = 'block';
                    });
                });
            });
        </script>
    @endpush
@endif
@endsection
