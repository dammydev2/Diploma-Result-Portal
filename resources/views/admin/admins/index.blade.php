@extends(backpack_view('blank'))

@section('content')
<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
    <h1 class="text-capitalize mb-0" bp-section="page-heading">Admins</h1>
    <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">Add other admins and control what each of them can do</p>
</section>
<section class="content container-fluid animated fadeIn" bp-section="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('page.admins.create') }}" class="btn btn-primary btn-sm">
                            <i class="la la-plus"></i> Add admin
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Access</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admins as $admin)
                                    <tr>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            @if ($admin->is_super_admin)
                                                <span class="badge bg-purple-lt">Super admin</span>
                                            @elseif ($admin->permissions->isEmpty())
                                                <span class="badge bg-secondary-lt">No permissions</span>
                                            @else
                                                @foreach ($admin->permissions as $permission)
                                                    <span class="badge bg-blue-lt">{{ $permission->label() }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('page.admins.edit', $admin) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="la la-edit"></i> Edit
                                            </a>
                                            @unless ($admin->is(auth()->user()))
                                                <form method="POST" action="{{ route('page.admins.destroy', $admin) }}" class="d-inline" onsubmit="return confirm('Remove {{ $admin->name }}\'s admin account?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="la la-trash"></i> Remove
                                                    </button>
                                                </form>
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
