@extends('layouts.master', ['title' => 'Mesin Biometrik'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Mesin Biometrik</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('biometric-device.index') }}" class="text-muted text-hover-primary">Mesin Biometrik</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('biometric-device.create') ? 'Tambah Perangkat' : 'Edit Perangkat' }}
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
                            <form id="biometric-device-form"
                                action="{{ request()->routeIs('biometric-device.create') ? route('biometric-device.store') : route('biometric-device.update', @$biometricDevice->id) }}"
                                method="POST">
                                @csrf
                                <x-form.put-method />

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="device_name">
                                        <span class="required">Nama Perangkat</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Contoh: Fingerprint Gerbang Utama, Face Scan Masjid"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="device_name" id="device_name"
                                        placeholder="Masukkan Nama Perangkat" value="{{ @$biometricDevice->device_name ?? old('device_name') }}" required />
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="device_ip">
                                        <span>IP Address Perangkat (Opsional)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Masukkan IP address perangkat di LAN lokal, misal 192.168.1.201"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="device_ip" id="device_ip"
                                        placeholder="Masukkan IP Address" value="{{ @$biometricDevice->device_ip ?? old('device_ip') }}" />
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="location">
                                        <span class="required">Lokasi Perangkat</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Lokasi penempatan fisik perangkat, misal: Kantor Administrasi, Masjid Putra"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="location" id="location"
                                        placeholder="Masukkan Lokasi Fisik" value="{{ @$biometricDevice->location ?? old('location') }}" required />
                                </div>

                                @if(isset($biometricDevice))
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span>Auth Token (Untuk Local Gateway)</span>
                                    </label>
                                    <div class="input-group input-group-solid">
                                        <input type="text" class="form-control" value="{{ $biometricDevice->auth_token }}" readonly id="auth-token-input" />
                                        <button class="btn btn-secondary" type="button" onclick="navigator.clipboard.writeText($('#auth-token-input').val()); toastr.success('Token copied to clipboard!')">Copy</button>
                                    </div>
                                    <span class="form-text text-muted">Salin token ini untuk dikonfigurasi pada Local Gateway Daemon agar perangkat lokal dapat terotentikasi saat mem-push data.</span>
                                </div>
                                @endif

                                <div class="separator mb-6"></div>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('biometric-device.index') }}" class="btn btn-sm btn-secondary me-3">Batal</a>
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
