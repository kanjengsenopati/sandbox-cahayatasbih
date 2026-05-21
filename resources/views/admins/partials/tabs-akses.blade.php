<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    @can('permission')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('permission.*') ? 'active fw-bolder' : '' }}" href="{{ route('permission.index') }}">
            <i class="fa-solid fa-key me-2"></i>Permission
        </a>
    </li>
    @endcan
    @can('Manage Role')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('role.*') ? 'active fw-bolder' : '' }}" href="{{ route('role.index') }}">
            <i class="fa-solid fa-user-shield me-2"></i>Role
        </a>
    </li>
    @endcan
    @can('Manage Admin')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.*') && !request()->routeIs('admin.audit') ? 'active fw-bolder' : '' }}" href="{{ route('admin.index') }}">
            <i class="fa-solid fa-users me-2"></i>Pengguna
        </a>
    </li>
    @endcan
</ul>
