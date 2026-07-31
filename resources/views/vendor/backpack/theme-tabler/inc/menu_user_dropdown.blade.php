<div class="nav-item dropdown pe-3">
    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
        <span class="avatar avatar-sm rounded-circle">
            <img class="avatar avatar-sm rounded-circle bg-transparent" src="{{ backpack_avatar_url(backpack_auth()->user()) }}"
                alt="{{ backpack_auth()->user()->name }}" onerror="this.style.display='none'"
                style="margin: 0;position: absolute;left: 0;z-index: 1;">
            <span class="avatar avatar-sm rounded-circle backpack-avatar-menu-container text-center">
                {{ backpack_user()->getAttribute('name') ? mb_substr(backpack_user()->name, 0, 1, 'UTF-8') : 'A' }}
            </span>
        </span>
        <div class="d-none d-xl-block ps-2">
            <div>{{ backpack_user()->name }}</div>
            <div class="mt-1 small text-muted">{{ trans('backpack::crud.admin') }}</div>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="la la-user me-2"></i>{{ __('Profile') }}</a>
        <a href="{{ route('security.edit') }}" class="dropdown-item"><i class="la la-key me-2"></i>{{ __('Change password') }}</a>

        <div class="dropdown-divider"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item"><i class="la la-lock me-2"></i>{{ trans('backpack::base.logout') }}</button>
        </form>
    </div>
</div>
