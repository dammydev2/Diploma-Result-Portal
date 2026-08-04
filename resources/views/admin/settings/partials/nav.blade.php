<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('page.settings.profile') ? 'active' : '' }}" href="{{ route('page.settings.profile') }}">
            Profile
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('page.settings.security') ? 'active' : '' }}" href="{{ route('page.settings.security') }}">
            Security
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('page.settings.institution') ? 'active' : '' }}" href="{{ route('page.settings.institution') }}">
            Institution
        </a>
    </li>
</ul>
