@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Institution settings</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Shown on printable documents, e.g. semester results and statements of result</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    @include('admin.settings.partials.nav')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('page.settings.institution.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="name">Institution name</label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name', $name) }}"
                                required
                                autofocus
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="address">Institution address</label>
                            <input
                                type="text"
                                class="form-control @error('address') is-invalid @enderror"
                                id="address"
                                name="address"
                                value="{{ old('address', $address) }}"
                            >
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h3 class="mb-1">Statement of result</h3>
                        <p class="text-muted small">
                            The background artwork and registrar sign-off shown on a student's
                            printed Statement of Result.
                        </p>

                        <div class="mb-3">
                            <label class="form-label" for="statement_background">Background image</label>
                            <div class="mb-2">
                                <img src="{{ $backgroundUrl }}" alt="Current background" class="border rounded" style="max-width: 220px;">
                            </div>
                            <input
                                type="file"
                                class="form-control @error('statement_background') is-invalid @enderror"
                                id="statement_background"
                                name="statement_background"
                                accept="image/*"
                            >
                            <div class="form-text">Replaces the current background. Leave blank to keep it.</div>
                            @error('statement_background')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="registrar_name">Registrar name</label>
                            <input
                                type="text"
                                class="form-control @error('registrar_name') is-invalid @enderror"
                                id="registrar_name"
                                name="registrar_name"
                                value="{{ old('registrar_name', $registrarName) }}"
                                required
                            >
                            @error('registrar_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="registrar_title">Registrar title</label>
                            <input
                                type="text"
                                class="form-control @error('registrar_title') is-invalid @enderror"
                                id="registrar_title"
                                name="registrar_title"
                                value="{{ old('registrar_title', $registrarTitle) }}"
                                required
                            >
                            @error('registrar_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="registrar_signature">Registrar signature</label>
                            <div class="mb-2">
                                <img src="{{ $signatureUrl }}" alt="Current signature" class="border rounded bg-light" style="max-width: 220px;">
                            </div>
                            <input
                                type="file"
                                class="form-control @error('registrar_signature') is-invalid @enderror"
                                id="registrar_signature"
                                name="registrar_signature"
                                accept="image/*"
                            >
                            <div class="form-text">
                                Ideally a PNG with a transparent background. Replaces the current
                                signature. Leave blank to keep it.
                            </div>
                            @error('registrar_signature')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
