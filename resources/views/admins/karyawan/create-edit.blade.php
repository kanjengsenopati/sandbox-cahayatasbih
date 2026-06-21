@extends('layouts.master', ['title' => 'Data Karyawan'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Karyawan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Pondok Mart (Outlet)</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        {{ isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan' }}
                    </li>
                </ul>
                <!--end::Breadcrumb-->
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid px-5">
            <div class="row g-7">
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush h-lg-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[24px]" style="border-radius: 24px; border: none;">
                        <!--begin::Card header-->
                        <div class="card-header pt-7">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    {{ isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <x-alert.alert-validation />
                            <form id="form-karyawan" action="{{ isset($karyawan) ? route('karyawan.update', $karyawan->id) : route('karyawan.store') }}" method="POST">
                                @csrf
                                <x-form.put-method />

                                <input type="hidden" name="mode" value="{{ request('mode') }}">
                                @if(request('outlet_id'))
                                    <input type="hidden" name="outlet_id" value="{{ request('outlet_id') }}">
                                @endif

                                <div class="row">
                                    <!-- Column 1 -->
                                    <div class="col-md-6">
                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label required" for="admin_id">
                                                <span>Pilih Pengguna / User (Scope Outlet)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih pengguna dengan Role Karyawan Outlet ( Non Kasir )"></i>
                                            </label>
                                            <select name="admin_id" id="admin_id" class="form-select form-select-solid" data-control="select2" data-placeholder="-- Pilih Pengguna --" required {{ isset($karyawan) ? 'disabled' : '' }}>
                                                <option value=""></option>
                                                @foreach ($admins as $admin)
                                                    <option value="{{ $admin->id }}" {{ (old('admin_id') ?? @$karyawan->admin_id) == $admin->id ? 'selected' : '' }}>
                                                        {{ $admin->name }} ({{ $admin->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(isset($karyawan))
                                                <input type="hidden" name="admin_id" value="{{ $karyawan->admin_id }}">
                                            @endif
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label required" for="outlet_id_select">
                                                <span>Establishment / Outlet</span>
                                            </label>
                                            @if(request('outlet_id'))
                                                <select class="form-select form-select-solid" id="outlet_id_select" disabled>
                                                    @foreach ($outlets as $outlet)
                                                        <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                                            {{ $outlet->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <select name="outlet_id" id="outlet_id_select" class="form-select form-select-solid" data-control="select2" required>
                                                    @foreach ($outlets as $outlet)
                                                        <option value="{{ $outlet->id }}" {{ (old('outlet_id') ?? @$karyawan->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                                            {{ $outlet->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label" for="kamar">
                                                <span>Kamar</span>
                                            </label>
                                            <input type="text" class="form-control form-control-solid" id="kamar" name="kamar" placeholder="Contoh: C4" value="{{ old('kamar', @$karyawan->kamar) }}" />
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label" for="jabatan">
                                                <span>Jabatan</span>
                                            </label>
                                            <input type="text" class="form-control form-control-solid" id="jabatan" name="jabatan" placeholder="Contoh: Supervisor, Cashier, Helper" value="{{ old('jabatan', @$karyawan->jabatan) }}" />
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label" for="section">
                                                <span>Section</span>
                                            </label>
                                            <input type="text" class="form-control form-control-solid" id="section" name="section" placeholder="Contoh: Weekend & Event, Weekdays" value="{{ old('section', @$karyawan->section) }}" />
                                        </div>
                                    </div>

                                    <!-- Column 2 -->
                                    <div class="col-md-6">
                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label required" for="gaji_bulan">
                                                <span>Jumlah Gaji (Bulan)</span>
                                            </label>
                                            <input type="text" class="form-control form-control-solid format-rupiah" id="gaji_bulan" name="gaji_bulan" placeholder="Contoh: 850.000" value="{{ old('gaji_bulan', isset($karyawan) ? (int)$karyawan->gaji_bulan : 0) }}" required />
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label required" for="gaji_hari">
                                                <span>Gaji Per-Day (Dihitung Otomatis)</span>
                                            </label>
                                            <input type="text" class="form-control form-control-solid format-rupiah" id="gaji_hari" name="gaji_hari" placeholder="Contoh: 26.600" value="{{ old('gaji_hari', isset($karyawan) ? (int)$karyawan->gaji_hari : 0) }}" readonly style="background-color: #f5f8fa; color: #5e6278;" required />
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label required" for="hari_kerja">
                                                <span>Hari Kerja</span>
                                            </label>
                                            <input type="number" class="form-control form-control-solid" id="hari_kerja" name="hari_kerja" placeholder="Contoh: 12" value="{{ old('hari_kerja', @$karyawan->hari_kerja ?? 12) }}" required min="0" />
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label" for="potongan_terlambat">
                                                <span>Potongan Terlambat (Per Shift - Dihitung Dinamis)</span>
                                            </label>
                                            <input type="text" class="form-control form-control-solid format-rupiah" id="potongan_terlambat" name="potongan_terlambat" value="{{ old('potongan_terlambat', isset($karyawan) ? (int)$karyawan->potongan_terlambat : 0) }}" readonly style="background-color: #f5f8fa; color: #5e6278;" />
                                        </div>

                                        <div class="fv-row mb-7">
                                            <label class="fs-6 fw-bold form-label" for="potongan_absen">
                                                <span>Potongan Absen (Per Hari - Dihitung Otomatis)</span>
                                            </label>
                                            <input type="text" class="form-control form-control-solid format-rupiah" id="potongan_absen" name="potongan_absen" value="{{ old('potongan_absen', isset($karyawan) ? (int)$karyawan->potongan_absen : 0) }}" readonly style="background-color: #f5f8fa; color: #5e6278;" />
                                        </div>
                                    </div>
                                </div>

                                <div class="separator mb-6"></div>

                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('karyawan.index', request()->only(['mode', 'outlet_id'])) }}" class="btn btn-sm btn-secondary me-3">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                    </button>
                                </div>
                                <!--end::Action buttons-->
                            </form>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Contacts-->
                </div>
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Fungsi memformat angka menjadi format rupiah (pemisah ribuan titik)
        function formatRupiah(angka) {
            if (!angka && angka !== 0) return '';
            let number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // Format otomatis ketika pengguna mengetik data
        $(document).on('input', '.format-rupiah', function() {
            let val = $(this).val().replace(/\./g, ''); // Hapus semua titik dulu
            if (val !== '') {
                $(this).val(formatRupiah(val));
            }
        });

        // Hitung otomatis Gaji Harian, Potongan Absen, Potongan Terlambat ketika Gaji Bulanan berubah
        $('#gaji_bulan').on('input', function() {
            let gajiBulan = parseInt($(this).val().replace(/\./g, '')) || 0;
            let gajiHari = Math.floor((gajiBulan / 30) / 100) * 100;
            
            $('#gaji_hari').val(formatRupiah(gajiHari));
            $('#potongan_absen').val(formatRupiah(gajiHari));
            $('#potongan_terlambat').val('0'); // Set default 0 karena dihitung dinamis di backend
        });

        // Format nilai awal saat halaman pertama kali dimuat (untuk edit / old input)
        $('.format-rupiah').each(function() {
            let val = $(this).val().replace(/\./g, '');
            if (val !== '' && !isNaN(val)) {
                $(this).val(formatRupiah(val));
            }
        });

        // Hapus pemisah ribuan titik sebelum form disubmit ke server
        $('#form-karyawan').on('submit', function() {
            $('.format-rupiah').each(function() {
                let val = $(this).val().replace(/\./g, '');
                $(this).val(val);
            });
        });
    });
</script>
@endpush
