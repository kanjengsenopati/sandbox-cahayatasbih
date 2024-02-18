@extends('layouts.master', ['title' => 'Data Perilaku'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Perilaku Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('student-counseling-score.index') }}">
                        <li class="breadcrumb-item text-muted">Perilaku</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('student-counseling-score.create') ? 'Tambah Perilaku' : 'Edit Perilaku'
                        }}
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
                            <form id="student-counseling-score" action="{{ request()->routeIs('student-counseling-score.create') ? route('student-counseling-score.store')
                                 : route('student-counseling-score.update', @$studentCounselingScore->id) }}"
                                method="POST" enctype="multipart/form-data">
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
                                        <option value="{{ $school->id }}" {{ @$studentCounselingScore->school_id ==
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
                                    <x-form.academic-year :value="@$studentCounselingScore->academic_year_id"
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
                                        <option value="GANJIL" {{ @$studentCounselingScore->semester == 'Ganjil' ?
                                            'selected' : '' }}>
                                            Ganjil</option>
                                        <option value="GENAP" {{ @$studentCounselingScore->semester == 'Genap' ?
                                            'selected'
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
                                    <x-form.student :value="@$studentCounselingScore->student_id"
                                        class="form-control form-control-solid" />
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="violation">
                                        <span class="required">Nama Pelanggaran</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Nama Pelanggaran"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" id="violation"
                                        name="violation" placeholder="Masukkan Nama Pelanggaran"
                                        value="{{ @$studentCounselingScore->violation ?? old('violation') }}"
                                        required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="score">
                                        <span class="required">Jumlah Skor</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Juara Ke"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="number" class="form-control form-control-solid" id="score" name="score"
                                        placeholder="Masukkan Jumlah Skor"
                                        value="{{ @$studentCounselingScore->score ?? old('score') }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="action">
                                        <span class="required">Tindakan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Tindakan yang Dilakukan"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" id="action" name="action"
                                        placeholder="Masukkan Tindakan yang Dilakukan"
                                        value="{{ @$studentCounselingScore->action ?? old('action') }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="note">
                                        <span class="required">Catatan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Catatan Atas Pelanggaran yang Dilakukan"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea class="form-control form-control-solid" id="note" name="note"
                                        placeholder="Masukkan Catatan Atas Pelanggaran yang Dilakukan"
                                        required>{{ @$studentCounselingScore->note ?? old('note') }}</textarea>
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
                                    <a href="{{ route('student-counseling-score.index') }}">
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