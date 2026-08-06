@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">{{ $admin ? 'Edit admin' : 'Add admin' }}</h1>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ $admin ? route('page.admins.update', $admin) : route('page.admins.store') }}">
                        @csrf
                        @if ($admin)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="name">Name</label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name', $admin?->name) }}"
                                required
                                autofocus
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email', $admin?->email) }}"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">{{ $admin ? 'New password' : 'Password' }}</label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                @unless ($admin) required @endunless
                            >
                            @if ($admin)
                                <div class="form-text">Leave blank to keep the current password.</div>
                            @endif
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
                            >
                        </div>

                        <hr class="my-4">

                        <div class="mb-3 form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="is_super_admin"
                                name="is_super_admin"
                                value="1"
                                @checked(old('is_super_admin', $admin?->is_super_admin))
                            >
                            <label class="form-check-label" for="is_super_admin">
                                Super admin
                            </label>
                            <div class="form-text">
                                Unrestricted access to everything, including Admins, Statement of Result, and
                                Institution settings — none of which can be granted through the checkboxes below.
                            </div>
                        </div>

                        <div class="mb-3" id="permissions-field">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                @foreach ($permissions as $permission)
                                    @php $oldPermissions = old('permissions', $admin?->permissions->map->value->all() ?? []); @endphp
                                    <div class="col-md-6 form-check mb-2">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            id="permission_{{ $permission->value }}"
                                            name="permissions[]"
                                            value="{{ $permission->value }}"
                                            @checked(in_array($permission->value, $oldPermissions, true))
                                        >
                                        <label class="form-check-label" for="permission_{{ $permission->value }}">
                                            {{ $permission->label() }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('page.admins.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('after_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var superAdminCheckbox = document.getElementById('is_super_admin');
                var permissionsField = document.getElementById('permissions-field');
                var permissionCheckboxes = permissionsField.querySelectorAll('input[type=checkbox]');

                function togglePermissions() {
                    var disabled = superAdminCheckbox.checked;
                    permissionsField.classList.toggle('opacity-50', disabled);
                    permissionCheckboxes.forEach(function (checkbox) {
                        checkbox.disabled = disabled;
                    });
                }

                superAdminCheckbox.addEventListener('change', togglePermissions);
                togglePermissions();
            });
        </script>
    @endpush
@endsection
