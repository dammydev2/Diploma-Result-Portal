@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Institution settings</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Shown on the header of printable documents, e.g. semester result sheets</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    @include('admin.settings.partials.nav')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('page.settings.institution.update') }}">
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

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
