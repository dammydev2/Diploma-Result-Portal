@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Profile settings</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Update your name and email address</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    @include('admin.settings.partials.nav')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('page.settings.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="name">Name</label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
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
                                value="{{ old('email', $user->email) }}"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4 border-danger">
                <div class="card-body">
                    <h3 class="card-title text-danger">Delete account</h3>
                    <p class="text-muted">Delete your account and all of its resources</p>

                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Delete account
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade @if ($errors->deleteAccount->isNotEmpty()) show @endif" id="deleteAccountModal" tabindex="-1" @if ($errors->deleteAccount->isNotEmpty()) style="display: block;" @endif>
    @if ($errors->deleteAccount->isNotEmpty())
        <div class="modal-backdrop fade show"></div>
    @endif
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('page.settings.profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">Are you sure you want to delete your account?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Once your account is deleted, all of its resources and data will be permanently deleted.
                        Please enter your password to confirm you would like to permanently delete your account.
                    </p>

                    <div class="mb-0">
                        <label class="form-label" for="delete-password">Password</label>
                        <input
                            type="password"
                            class="form-control @error('password', 'deleteAccount') is-invalid @enderror"
                            id="delete-password"
                            name="password"
                        >
                        @error('password', 'deleteAccount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
