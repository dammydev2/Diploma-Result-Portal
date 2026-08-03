@if ($crud->hasAccess('update', $entry))
    @if ($entry->isSuspended)
        <form method="POST" action="{{ route('student.reactivate', $entry->getKey()) }}" class="d-inline">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-sm btn-link text-success p-0">
                <i class="la la-check-circle"></i> <span>{{ __('Reactivate') }}</span>
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('student.suspend', $entry->getKey()) }}" class="d-inline">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                <i class="la la-ban"></i> <span>{{ __('Suspend') }}</span>
            </button>
        </form>
    @endif
@endif
