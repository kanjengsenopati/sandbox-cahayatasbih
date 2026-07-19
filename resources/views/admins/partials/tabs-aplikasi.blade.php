<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    @can('Manage Pengaturan Aplikasi')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('application-setting.*') ? 'active fw-bolder' : '' }}" href="{{ route('application-setting.index') }}">
            <i class="fa-solid fa-sliders me-2"></i>Pengaturan Aplikasi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('working-shift.*') ? 'active fw-bolder' : '' }}" href="{{ route('working-shift.index') }}">
            <i class="fa-solid fa-clock me-2"></i>Shift Presensi
        </a>
    </li>
    @endcan
    @can('Manage Biometric')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('biometric-device.*') ? 'active fw-bolder' : '' }}" href="{{ route('biometric-device.index') }}">
            <i class="fa-solid fa-fingerprint me-2"></i>Mesin Biometrik
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('biometric-mapping.*') ? 'active fw-bolder' : '' }}" href="{{ route('biometric-mapping.index') }}">
            <i class="fa-solid fa-user-gear me-2"></i>Pemetaan Biometrik
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
