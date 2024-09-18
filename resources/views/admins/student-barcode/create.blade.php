@extends('layouts.master', ['title' => 'Data Barcode Santri'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Barcode Santri</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('student-barcode.index') }}" class="text-muted text-hover-primary">Data
                            Barcode Santri</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Cetak Barcode Santri</li>
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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">Cetak Barcode Santri
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form" action="{{ route('student-barcode.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="UPT">
                                        <span class="required">Unit Pendidikan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Silahkan memilih UPT"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="school_id" name="school_id"
                                        class="form-control form-control-solid flex-grow-1" id="school_id">
                                        <option value="">Pilih Unit Pendidikan</option>
                                        @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" {{ request('school_id')==$school->
                                            id ?
                                            'selected' : '' }}>
                                            {{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3 me-3 col-2" for="name">
                                        <span class="required">NIS/NISN/Nama</span>
                                    </label>
                                    <select name="student_ids[]" id="student_id"
                                        class="form-select form-select-solid mb-3" id="student_id"
                                        data-control="select2" data-allow-clear="true" data-placeholder="Pilih Santri"
                                        multiple="multiple" required>
                                    </select>
                                    <!--end::Label-->
                                    <!--begin::Input-->

                                    <div class="d-flex gap-3">
                                        <input type="checkbox" id="select-all">
                                        <label style="font-size: 14px;" class="cursor-pointer" for="select-all">Select
                                            All</label>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('student-barcode.index') }}">
                                        <button type="button" data-kt-contacts-type="cancel"
                                            class="btn btn-sm btn-secondary me-3">Cancel</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Cetak Barcode</span>
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
    $(".select2").select2();
        $(document).ready(function() {
            $("#select-all").click(function() {
                if ($("#select-all").is(':checked')) { //select all
                    $(".form-select").find('option').prop("selected", true);
                    $(".form-select").trigger('change');
                } else { //deselect all
                    $(".form-select").find('option').prop("selected", false);
                    $(".form-select").trigger('change');
                }
            });

            $('#select2').on('change',function(){
                let selected =  $(this).val();
                if(selected == ''){
                    $("#select-all").prop('checked',false);
                }
            })
        })
</script>
<script>
    $(document).ready(function() {
    // Function to fetch student data based on selected school
    function fetchStudentData() {
    var school_id = $('#school_id').val();
    if (school_id) {
    $.ajax({
    url: "{{ route('select2') }}",
    dataType: 'json',
    delay: 300,
    data: {
    search: '', // Assuming you need a default search term
    data_type: "STUDENT_ACTIVE_BY_SCHOOL",
    school_id: school_id
    },
    success: function (data) {
    var results = $.map(data, function (item) {
    let displayText = (item.nis ? item.nis + ' - ' : '') +
    item.name + ' - ' +
    (item.classroom?.name ? item.classroom.name : '');
    return {
    text: displayText,
    id: item.id
    };
    });
    
    $('#student_id').empty().select2({
    data: results,
    cache: true
    });
    },
    cache: true
    });
    } else {
    $('#student_id').empty();
    }
    }
    
    // Bind the change event to the fetchStudentData function
    $('#school_id').change(fetchStudentData);
    
    // Call the function on page load
    fetchStudentData();
    });
</script>
@endpush