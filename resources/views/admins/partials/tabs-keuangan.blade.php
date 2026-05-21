<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    @can('Manage Jenis Bayar')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('bill-type.*') ? 'active fw-bolder' : '' }}" href="{{ route('bill-type.index') }}">
            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Jenis Bayar
        </a>
    </li>
    @endcan
    @can('Manage Item Bayar')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('bill-item.*') ? 'active fw-bolder' : '' }}" href="{{ route('bill-item.index') }}">
            <i class="fa-solid fa-list-check me-2"></i>Item Bayar
        </a>
    </li>
    @endcan
    @can('Manage Bank')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('bank.*') ? 'active fw-bolder' : '' }}" href="{{ route('bank.index') }}">
            <i class="fa-solid fa-piggy-bank me-2"></i>Bank
        </a>
    </li>
    @endcan
</ul>
