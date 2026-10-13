@php
    $showBiDashboard = !$isOutletUser && ($canViewBiDashboard ?? (auth()->check() && method_exists(auth()->user(), 'canAccessBiDashboard') && auth()->user()->canAccessBiDashboard()));
    $pageTitle = $isOutletUser
        ? 'Dashboard Outlet'
        : ($showBiDashboard ? 'Dashboard Business Intelligence' : 'Dashboard Operasional');
@endphp
@extends('layouts.master', ['title' => $pageTitle])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar" style="backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.85);">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-2 mb-lg-0">
                <x-text.h1 class="my-1 d-flex align-items-center gap-2">
                    {{ $isOutletUser ? 'Dashboard Outlet' : ($showBiDashboard ? 'Executive Business Intelligence' : 'Workspace Operasional') }}
                </x-text.h1>
                @if(!$isOutletUser && isset($activeAcademicYearName))
                    <span class="h-20px border-gray-300 border-start mx-3"></span>
                    <span class="badge rounded-pill px-3 py-2" style="background-color: rgba(37, 99, 235, 0.1); color: #2563EB; font-weight: 700; font-size: 11px;">
                        TA: {{ $activeAcademicYearName }}
                    </span>
                @endif
            </div>
            @if($isOutletUser)
                @include('layouts.partials.outlet_switcher')
            @elseif($showBiDashboard)
                <form action="{{ route('dashboard') }}" method="GET" class="d-flex flex-nowrap align-items-center gap-2 my-1">
                    @if(isset($schools) && $schools->count() > 1)
                        <select name="school_id" onchange="this.form.submit()" class="form-select form-select-sm border-0 fw-bold w-auto" style="border-radius: 14px; background-color: #f1f5f9; color: #1e293b; min-width: 180px; max-width: 260px; height: 36px; text-overflow: ellipsis;">
                            <option value="all">Semua Unit (UPT)</option>
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}" {{ ($selectedSchoolId ?? null) === $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @if(isset($academicYears) && $academicYears->count() > 0)
                        <select name="academic_year_id" onchange="this.form.submit()" class="form-select form-select-sm border-0 fw-bold w-auto" style="border-radius: 14px; background-color: #f1f5f9; color: #1e293b; min-width: 175px; max-width: 220px; height: 36px; text-overflow: ellipsis;">
                            <option value="all" {{ ($selectedAcademicYearId ?? '') === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ ($selectedAcademicYearId ?? '') === $ay->id ? 'selected' : '' }}>
                                    TA {{ $ay->name }}{{ $ay->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </form>
            @endif
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl px-5">
            @if($isOutletUser)
                @include('admins.dashboard.outlet')
            @elseif($showBiDashboard)
                @include('admins.dashboard.academic')
            @else
                <div class="card border-0 mb-5" style="border-radius: 24px; box-shadow: 0 8px 30px rgb(0, 0, 0, 0.04); background: #ffffff;">
                    <div class="card-body p-5">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <x-text.label>BACKOFFICE WORKSPACE</x-text.label>
                                <x-text.h1 class="mt-1">Selamat Datang, {{ auth()->user()->name ?? 'Pengguna' }}</x-text.h1>
                                <x-text.body class="mt-1">
                                    Anda masuk menggunakan hak akses <strong>{{ auth()->user()->role_name ?? 'Staff' }}</strong>. Silakan gunakan navigasi menu di sebelah kiri sesuai otorisasi tugas Anda.
                                </x-text.body>
                            </div>
                            <span class="badge rounded-pill px-3 py-2" style="background-color: rgba(16, 185, 129, 0.1); color: #10B981; font-weight: 700; font-size: 11.5px;">
                                Status Akun: Aktif & Terverifikasi
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection