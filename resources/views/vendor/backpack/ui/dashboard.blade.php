@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Welcome, {{ backpack_user()->name }}</h1>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row row-deck">
        @can('manage_students')
            <div class="col-md-4">
                <a href="{{ backpack_url('students') }}" class="card card-link text-reset text-decoration-none">
                    <div class="card-body">
                        <div class="subheader">Total students</div>
                        <div class="h1 mb-0">{{ \App\Models\Student::query()->count() }}</div>
                    </div>
                </a>
            </div>
        @endcan

        @can('manage_programmes')
            <div class="col-md-4">
                <a href="{{ backpack_url('programme') }}" class="card card-link text-reset text-decoration-none">
                    <div class="card-body">
                        <div class="subheader">Total programmes</div>
                        <div class="h1 mb-0">{{ \App\Models\Programme::query()->count() }}</div>
                    </div>
                </a>
            </div>
        @endcan

        @can('super_admin_only')
            <div class="col-md-4">
                <a href="{{ route('page.admins.index') }}" class="card card-link text-reset text-decoration-none">
                    <div class="card-body">
                        <div class="subheader">Total admins</div>
                        <div class="h1 mb-0">{{ \App\Models\User::query()->count() }}</div>
                    </div>
                </a>
            </div>
        @endcan
    </div>
</section>
@endsection
