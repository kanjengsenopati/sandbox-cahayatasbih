@extends('layouts.master', ['title' => 'Shift Presensi'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Shift Presensi</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('working-shift.index', request()->only(['mode', 'outlet_id'])) }}" class="text-muted text-hover-primary">Shift Presensi</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('working-shift.create') ? 'Tambah Shift' : 'Edit Shift' }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <div class="row g-7">
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <div class="card-body pt-5">
                            <x-alert.alert-validation />
                            <form id="working-shift-form"
                                action="{{ request()->routeIs('working-shift.create') ? route('working-shift.store', request()->only(['mode', 'outlet_id'])) : route('working-shift.update', array_merge([@$workingShift->id], request()->only(['mode', 'outlet_id']))) }}"
                                method="POST">
                                @csrf
                                <x-form.put-method />

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Shift</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Contoh: Shift Pagi Karyawan, Presensi Harian Santri"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="name" id="name"
                                        placeholder="Masukkan Nama Shift" value="{{ @$workingShift->name ?? old('name') }}" required />
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="target_type">
                                        <span class="required">Target Presensi Untuk Siapa</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih target kelompok pengguna untuk shift ini"></i>
                                    </label>
                                    <select class="form-select form-select-solid" name="target_type" id="target_type" required>
                                        <option value="">Pilih Target Kelompok</option>
                                        <option value="siswa_santri" {{ (@$workingShift->target_type ?? old('target_type')) == 'siswa_santri' ? 'selected' : '' }}>Siswa dan Santri</option>
                                        <option value="karyawan" {{ (@$workingShift->target_type ?? old('target_type')) == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                        <option value="user" {{ (@$workingShift->target_type ?? old('target_type')) == 'user' ? 'selected' : '' }}>User / Wali</option>
                                    </select>
                                </div>

                                <div class="fv-row mb-7" id="assigned_users_container" style="display: none;">
                                    <label class="fs-6 fw-bold form-label mt-3" for="assigned_users">
                                        <span>Pilih Karyawan / Guru / Kasir</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih karyawan/guru/kasir yang terikat pada shift ini"></i>
                                    </label>
                                    <select class="form-select form-select-solid" name="assigned_users[]" id="assigned_users" data-control="select2" data-placeholder="Pilih Karyawan / Guru / Kasir..." data-allow-clear="true" multiple="multiple">
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp['value'] }}" {{ in_array($emp['value'], @$workingShift->assigned_users ?? []) ? 'selected' : '' }}>
                                                {{ $emp['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Hari Aktif (Pilih Hari)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih hari-hari aktif berlakunya shift presensi ini"></i>
                                    </label>
                                    <div class="d-flex flex-wrap gap-5 mt-2">
                                        @php
                                            $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                            $selectedDays = @$workingShift->days ?? old('days') ?? [];
                                        @endphp
                                        @foreach($daysList as $day)
                                            <label class="form-check form-check-sm form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" 
                                                    {{ in_array($day, $selectedDays) ? 'checked' : '' }} />
                                                <span class="form-check-label text-gray-700 fw-bold">{{ $day }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="row row-cols-1 row-cols-sm-2 mb-7">
                                    <div class="col">
                                        <div class="fv-row">
                                            <label class="fs-6 fw-bold form-label mt-3" for="start_time">
                                                <span class="required">Jam Masuk (Check-in)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Format HH:MM (Jam:Menit)"></i>
                                            </label>
                                            <input type="time" class="form-control form-control-solid" name="start_time" id="start_time"
                                                value="{{ isset($workingShift) ? \Carbon\Carbon::parse($workingShift->start_time)->format('H:i') : old('start_time') }}" required />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fv-row">
                                            <label class="fs-6 fw-bold form-label mt-3" for="end_time">
                                                <span class="required">Jam Keluar (Check-out)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Format HH:MM (Jam:Menit)"></i>
                                            </label>
                                            <input type="time" class="form-control form-control-solid" name="end_time" id="end_time"
                                                value="{{ isset($workingShift) ? \Carbon\Carbon::parse($workingShift->end_time)->format('H:i') : old('end_time') }}" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="grace_period">
                                        <span class="required">Toleransi Keterlambatan (Menit)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Jumlah menit keterlambatan yang ditoleransi sebelum dianggap terlambat"></i>
                                    </label>
                                    <input type="number" class="form-control form-control-solid" name="grace_period" id="grace_period"
                                        placeholder="Contoh: 15" value="{{ @$workingShift->grace_period ?? old('grace_period', 15) }}" min="0" required />
                                </div>

                                <div class="separator mb-6"></div>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('working-shift.index', request()->only(['mode', 'outlet_id'])) }}" class="btn btn-sm btn-secondary me-3">Batal</a>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        function toggleAssignedUsers() {
            const targetType = $('#target_type').val();
            if (targetType === 'karyawan') {
                $('#assigned_users_container').slideDown();
            } else {
                $('#assigned_users_container').slideUp();
                $('#assigned_users').val([]).trigger('change');
            }
        }

        $('#target_type').on('change', toggleAssignedUsers);
        toggleAssignedUsers(); // Trigger on load
    });
</script>
@endpush
