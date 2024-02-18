@extends('layouts.master', ['title' => 'Data Prestasi'])
@push('css')

@endpush
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Prestasi Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('student-achievement.index') }}">
                        <li class="breadcrumb-item text-muted">Prestasi</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('student-achievement.create') ? 'Tambah Prestasi' : 'Edit Prestasi' }}
                    </li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <!--begin::Card title-->
                            <div class="card-title">

                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form id="student-achievement" action="{{ request()->routeIs('student-achievement.create') ? route('student-achievement.store')
                                 : route('student-achievement.update', @$studentAchievement->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="school_id">
                                        <span class="required">Sekolah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tahun Ajaran"></i>
                                    </label>
                                    <select name="school_id" id="school_id" class="form-select form-select-solid">
                                        <option value="">Pilih Sekolah</option>
                                        @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" {{ @$studentAchievement->school_id ==
                                            $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Label-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="classroom_id">
                                        <span class="required">Kelas</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tahun Ajaran"></i>
                                    </label>
                                    <select name="classroom_id" id="classroom_id" class="form-select form-select-solid">
                                        <option value="">Pilih Kelas</option>
                                    </select>
                                    <!--end::Label-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="academic_year_id">
                                        <span class="required">Tahun Ajaran</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tahun Ajaran"></i>
                                    </label>
                                    <x-form.academic-year :value="@$studentAchievement->academic_year_id"
                                        class="form-control form-control-solid" />
                                    <!--end::Label-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="semester">
                                        <span class="required">Semester</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Semester"></i>
                                    </label>
                                    <select class="form-select form-select-solid" id="semester" name="semester">
                                        <option value="">Pilih Semester</option>
                                        <option value="GANJIL" {{ @$studentAchievement->semester == 'Ganjil' ?
                                            'selected' : '' }}>
                                            Ganjil</option>
                                        <option value="GENAP" {{ @$studentAchievement->semester == 'Genap' ? 'selected'
                                            : '' }}>
                                            Genap</option>
                                    </select>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="student_id">
                                        <span class="required">Nama Santri</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Nama Santri"></i>
                                    </label>
                                    <x-form.student :value="@$tahfidz->student_id"
                                        class="form-control form-control-solid" />
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="title">
                                        <span class="required">Nama Prestasi Santri</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Nama Prestasi Santri"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" id="title" name="title"
                                        placeholder="Masukkan Nama Prestasi Santri"
                                        value="{{ @$studentAchievement->title ?? old('title') }}" required />
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="champion">
                                        <span class="required">Juara Ke</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Juara Ke"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" id="champion"
                                        name="champion" placeholder="Masukkan Juara Ke"
                                        value="{{ @$tahfidz->champion ?? old('champion') }}" required />
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="level">
                                        <span class="required">Tingkat Prestasi Santri</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tingkat Prestasi Santri"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select class="form-select form-select-solid" id="level" name="level">
                                        <option value="">Pilih Tingkat Prestasi</option>
                                        <option value="Kecamatan" {{ @$studentAchievement->level == 'Kecamatan' ?
                                            'selected' : '' }}>
                                            Kecamatan</option>
                                        <option value="Kabupaten" {{ @$studentAchievement->level == 'Kabupaten' ?
                                            'selected' : '' }}>
                                            Kabupaten</option>
                                        <option value="Provinsi" {{ @$studentAchievement->level == 'Provinsi' ?
                                            'selected' : '' }}>
                                            Provinsi</option>
                                        <option value="Nasional" {{ @$studentAchievement->level == 'Nasional' ?
                                            'selected' : '' }}>
                                            Nasional</option>
                                        <option value="Internasional" {{ @$studentAchievement->level == 'Internasional'
                                            ?
                                            'selected' : '' }}>
                                            Internasional</option>
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="reward">
                                        <span class="required">Hadiah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Hadiah"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" id="reward" name="reward"
                                        placeholder="Masukkan Jumlah Hadiah"
                                        value="{{ @$studentAchievement->reward ?? old('reward') }}" required />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6">
                                </div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('student-achievement.index') }}">
                                        <button type="button" data-kt-contacts-type="cancel"
                                            class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                    <!--end::Button-->
                                </div>
                                <!--end::Action buttons-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Contacts-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Contacts App- Add New Contact-->
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
    $(document).ready(function () {
            $('#school_id').change(function () {
                var school_id = $(this).val();
                var url = '{{ route('student-achievement.get-classroom') }}';
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        school_id: school_id
                    },
                    success: function (response) {
                        $('#classroom_id').empty();
                        $('#classroom_id').append('<option value="">Pilih Kelas</option>');
                        $.each(response.data, function (key, value) {
                            $('#classroom_id').append('<option value="' + value.id + '">' + value.name +
                                '</option>');
                        });
                    }
                });
            });
        });
</script>
@endpush