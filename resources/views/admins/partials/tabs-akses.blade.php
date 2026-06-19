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
            <i class="fa-solid fa-user-shield me-2"></i>Peran
        </a>
    </li>
    @endcan
    @can('Manage Admin')
    <li class="nav-item">
        <a class="nav-link {{ (request()->routeIs('admin.*') && !request()->routeIs('admin.audit') && !request()->routeIs('admin.scope-akses*')) ? 'active fw-bolder' : '' }}" href="{{ route('admin.index') }}">
            <i class="fa-solid fa-users me-2"></i>Pengguna
        </a>
    </li>
    @endcan
    @can('Manage Admin')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.scope-akses*') ? 'active fw-bolder' : '' }}" href="{{ route('admin.scope-akses') }}">
            <i class="fa-solid fa-laptop-code me-2"></i>Scope Akses Aplikasi
        </a>
    </li>
    @endcan
    @can('Manage Sekolah')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('school.*') ? 'active fw-bolder' : '' }}" href="{{ route('school.index') }}">
            <i class="fa-solid fa-school me-2"></i>Scope Wilayah UPT
        </a>
    </li>
    @endcan
    @can('Manage Outlet')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('outlet.*') ? 'active fw-bolder' : '' }}" href="{{ route('outlet.index') }}">
            <i class="fa-solid fa-store me-2"></i>Scope Pondok Mart
        </a>
    </li>
    @endcan
</ul>
