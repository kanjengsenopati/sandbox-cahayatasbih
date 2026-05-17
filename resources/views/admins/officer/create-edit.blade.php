@extends('layouts.master', ['title' => 'Data Petugas / Pengurus'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Petugas & Pengurus</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <a class="breadcrumb-item" href="{{ route('officer.index') }}">
                        <li class="text-muted">Petugas</li>
                    </a>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('officer.create') ? 'Tambah Petugas' : 'Edit Petugas' }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <div class="row g-7">
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <div class="card-body pt-5">
                            <x-alert.alert-validation />
                            <form id="officer-form"
                                action="{{ request()->routeIs('officer.create') ? route('officer.store') : route('officer.update', @$officer->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />

                                <!-- Name Input -->
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Petugas / Pengurus</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="name" id="name"
                                        placeholder="Masukkan nama lengkap beserta gelar" value="{{ @$officer->name ?? old('name') }}"
                                        required />
                                </div>

                                <!-- Position Input -->
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="position">
                                        <span class="required">Jabatan</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="position" id="position"
                                        placeholder="Contoh: Kepala Kepengasuhan, Bendahara Pondok" value="{{ @$officer->position ?? old('position') }}"
                                        required />
                                </div>

                                <!-- Duty Input -->
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="duty">
                                        <span class="required">Tugas / Deskripsi</span>
                                    </label>
                                    <textarea class="form-control form-control-solid" name="duty" id="duty" rows="3"
                                        placeholder="Jelaskan deskripsi tugas dan wewenang yang bisa dikonsultasikan oleh wali"
                                        required>{{ @$officer->duty ?? old('duty') }}</textarea>
                                </div>

                                <!-- Phone Input -->
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="phone">
                                        <span class="required">Nomor WhatsApp</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Gunakan format internasional bersih tanpa spasi atau tanda hubung, contoh: 6281234567890"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="phone" id="phone"
                                        placeholder="Contoh: 6281234567890" value="{{ @$officer->phone ?? old('phone') }}"
                                        required />
                                </div>

                                <!-- Photo Upload -->
                                <div class="fv-row mb-6">
                                    <x-form.image-upload label="Foto Petugas (Opsional)" name="photo"
                                        :value="@$officer->photo ?? null" />
                                </div>

                                <div class="separator mb-6"></div>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('officer.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
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
    <!--end::Post-->
</div>
@endsection
