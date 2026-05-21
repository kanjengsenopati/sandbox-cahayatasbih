<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    @can('Manage Pengaturan Aplikasi')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('application-setting.*') ? 'active fw-bolder' : '' }}" href="{{ route('application-setting.index') }}">
            <i class="fa-solid fa-sliders me-2"></i>Pengaturan Aplikasi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student-card-setting.*') ? 'active fw-bolder' : '' }}" href="{{ route('student-card-setting.index') }}">
            <i class="fa-solid fa-id-card me-2"></i>Desain & Cetak Kartu
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.audit') ? 'active fw-bolder' : '' }}" href="{{ route('admin.audit') }}">
            <i class="fa-solid fa-rotate me-2"></i>Audit & Sinkronisasi
        </a>
    </li>
    @endcan
    @can('Manage Menu Aplikasi')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('application-menu.*') ? 'active fw-bolder' : '' }}" href="{{ route('application-menu.index') }}">
            <i class="fa-solid fa-ellipsis-vertical me-2"></i>Menu Aplikasi
        </a>
    </li>
    @endcan
</ul>
