@extends('layouts.master', ['title' => 'Data Siswa'])
@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('student.index') }}">
                        <li class="breadcrumb-item text-muted">Siswa</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('student.create') ? 'Tambah Siswa' : 'Edit Siswa' }}</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Navbar-->
            {{-- @if (request()->routeIs('student.edit'))
            @include('admins.student.components.header')
            @endif --}}
            <!--end::Navbar-->
            <!--begin::Basic info-->
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_profile_details" aria-expanded="true"
                    aria-controls="kt_account_profile_details">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bolder m-0">Data Siswa</h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        <!--begin::Input group-->
                        <x-alert.alert-validation />
                        <form id="student"
                            action="{{ request()->routeIs('student.create') ? route('student.store') : route('student.update', $student->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <x-form.put-method />
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- First Column -->
                                    <div class="fv-row mb-6">
                                        <x-form.image-upload label="Foto Siswa" name="avatar"
                                            :value="@$student->avatar ?? null" />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="user_id">
                                            <span class="required">Nama Wali Siswa</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Nama Wali Siswa"></i>
                                        </label>
                                        <x-form.user :value="@$student->user_id"
                                            class="form-control form-control-solid" />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="gender">
                                            <span class="required">Jenis Kelamin</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Jenis Kelamin Siswa"></i>
                                        </label>
                                        <select name="gender" class="form-select form-select-solid" id="gender"
                                            data-control="select2" data-placeholder="Pilih Jenis Kelamin"
                                            data-allow-clear="true" data-hide-search="true">
                                            <option value="L" {{ @$user->gender == 'L' ? 'selected' : '' }}> Laki-Laki
                                            </option>
                                            <option value="P" {{ @$user->gender == 'P' ? 'selected' : '' }}> Perempuan
                                            </option>
                                        </select>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="born_place">
                                            <span class="required">Tempat Lahir</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Tempat Lahir"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="born_place"
                                            name="born_place" placeholder="Masukkan Tempat Lahir"
                                            value="{{ @$student->born_place ?? old('born_place') }}" required />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="status">
                                            <span class="required">Status</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Status Siswa"></i>
                                        </label>
                                        <select name="status" class="form-select form-select-solid" id="status"
                                            data-control="select2" data-placeholder="Pilih Status"
                                            data-allow-clear="true" data-hide-search="true">
                                            <option value="ACTIVE" {{ @$student->status == "ACTIVE" ? 'selected' : ''
                                                }}>
                                                Aktif </option>
                                            <option value="INACTIVE" {{ @$student->status == "INACTIVE" ? 'selected' :
                                                ''
                                                }}>
                                                Tidak Aktif </option>
                                            <option value="GRADUATED" {{ @$student->status == "GRADUATED" ? 'selected' :
                                                '' }}>
                                                Lulus </option>
                                            <option value="DROPPED_OUT" {{ @$student->status == "DROPPED_OUT" ?
                                                'selected' :
                                                '' }}>
                                                Keluar </option>
                                            <option value="TRANSFERRED" {{ @$student->status == "TRANSFERRED" ?
                                                'selected' :
                                                '' }}>
                                                Pindah </option>
                                        </select>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="asrama_host_id">
                                            <span>Pembina / Host Asrama</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Host Asrama/Pembimbing Santri"></i>
                                        </label>
                                        <select name="asrama_host_id" class="form-select form-select-solid" id="asrama_host_id"
                                            data-control="select2" data-placeholder="Pilih Pembina Asrama"
                                            data-allow-clear="true">
                                            <option value="">Tidak ada pembina</option>
                                            @foreach ($hosts as $host)
                                            <option value="{{ $host->id }}" {{ @$student->asrama_host_id == $host->id ? 'selected' : '' }}>
                                                {{ $host->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="asrama_name">
                                            <span>Nama Asrama</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nama Hunian Asrama / Kamar Santri"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="asrama_name"
                                            name="asrama_name" placeholder="Masukkan Nama Asrama"
                                            value="{{ @$student->asrama_name ?? old('asrama_name') }}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Second Column -->
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="name">
                                            <span class="required">Nama Siswa</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="masukkan nama siswa"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="name" name="name"
                                            placeholder="Masukkan Nama Siswa"
                                            value="{{ @$student->name ?? old('name') }}" required />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="birth_date">
                                            <span class="required">Tanggal Lahir</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Tanggal Lahir Siswa"></i>
                                        </label>
                                        <input type="date" class="form-control form-control-solid" id="birth_date"
                                            name="birth_date" placeholder="Masukkan Tanggal Lahir Siswa"
                                            value="{{ @$student->birth_date ?? old('birth_date') }}" required />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="nisn">
                                            <span class="required">NISN (Nomor Induk Siswa Nasional)</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nisn Siswa"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="nisn" name="nisn"
                                            placeholder="Masukkan Nisn Siswa"
                                            value="{{ @$student->nisn ?? old('nisn') }}" />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="nis">
                                            <span class="required">NIS (Nomor Induk Santri)</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nomor Induk Santri"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="nis" name="nis"
                                            placeholder="Masukkan Nis Siswa" value="{{ @$student->nis ?? old('nis') }}"
                                            required />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="school_id">
                                            <span class="required">Sekolah</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Sekolah yang akan di daftarkan"></i>
                                        </label>
                                        <select name="school_id" class="form-select form-select-solid" id="school_id"
                                            data-control="select2" data-allow-clear="true" data-hide-search="true">
                                            <option value="">Pilih Sekolah</option>
                                            @foreach ($schools as $school)
                                            <option value="{{ $school->id }}" {{ @$student->school_id == $school->id ?
                                                'selected' : '' }}>
                                                {{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="school_id">
                                            <span class="required">Kelas</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih kelas yang akan di daftarkan"></i>
                                        </label>
                                        {{-- get classroom_id by school_id --}}
                                        <select name="classroom_id" class="form-select form-select-solid"
                                            id="classroom_id" data-control="select2" data-allow-clear="true"
                                            data-hide-search="true">
                                            <option value="{{ @$student->classroom_id }}" selected>{{
                                                @$student->classroom->name ?? 'Pilih
                                                Kelas' }}</option>
                                        </select>
                                    </div>

                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="address">
                                            <span class="required">Alamat</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Alamat Siswa"></i>
                                        </label>
                                        <textarea class="form-control form-control-solid" id="address" name="address"
                                            placeholder="Masukkan Alamat Siswa"
                                            required>{{ @$student->address ?? old('address') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Separator-->
                            <div class="separator mb-6"></div>
                            <!--end::Separator-->

                    </div>
                    <!--end::Card body-->
                    <!--begin::Actions-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <a href="{{ route('student.index') }}"
                            class="btn btn-light btn-active-light-primary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary"
                            id="kt_account_profile_details_submit">Simpan</button>
                    </div>
                    <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Basic info-->
            <!--end::Modals-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $('#school_id').on('change', function () {
        var school_id = $(this).val();
        if (school_id) {
            $.ajax({
                url: '{{ route("select2") }}',
                type: "GET",
                data: {
                    school_id: school_id,
                    data_type: 'CLASSROOM_BY_SCHOOL'
                },
                dataType: "json",
                success: function (data) {
                    $('#classroom_id').empty();
                    // if data null show tidak ada kelas
                    if (data.length == 0) {
                        $('#classroom_id').append(
                            '<option value="" selected disabled>Tidak ada kelas</option>');
                    } else {
                        $.each(data, function (key, value) {
                        $('#classroom_id').append('<option value="' + value.id + '">' + value.name +
                            '</option>');
                        });
                    }
                },
            });
        } else {
            $('#classroom_id').empty();
        }
    }); 

</script>
@endpush