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
                                <div class="col-md-4">
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

                                </div>
                                <div class="col-md-4">
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
                                        <label class="fs-6 fw-bold form-label" for="nickname">
                                            <span>Nama Panggilan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan nama panggilan siswa"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="nickname" name="nickname"
                                            placeholder="Masukkan Nama Panggilan"
                                            value="{{ @$student->nickname ?? old('nickname') }}" />
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
                                </div>
                                <div class="col-md-4">
                                    <!-- Third Column -->
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label required" for="classroom_id">Pilih Kelas</label>
                                        <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
                                            <button type="button" id="btn_toggle_classroom" class="btn btn-sm btn-light-primary text-start flex-grow-1 d-flex justify-content-between align-items-center">
                                                <span id="selected_class_name">{{ @$student->classroom->name ?? 'Pilih Kelas' }}</span>
                                                <i class="fas fa-chevron-down fs-7"></i>
                                            </button>
                                        </div>

                                        <!-- Hidden Input for Form Submission -->
                                        <input type="hidden" name="classroom_id" id="classroom_id" value="{{ @$student->classroom_id }}" required />

                                        <!-- 3-Column Grid Container -->
                                        <div class="card card-bordered bg-light p-2.5 rounded-[16px] border-gray-300 d-none mt-2" id="classroom_3col_card" style="max-height: 240px; overflow-y: auto;">
                                            <div class="row g-2" id="classroom_3col_grid">
                                                <!-- Populated dynamically via render3ColClassrooms -->
                                            </div>
                                        </div>
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
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="city">
                                            <span>Kota / Kabupaten</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Kota / Kabupaten"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="city" name="city"
                                            placeholder="Masukkan Kota / Kabupaten"
                                            value="{{ @$student->city ?? old('city') }}" />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="province">
                                            <span>Provinsi</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Provinsi"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="province" name="province"
                                            placeholder="Masukkan Provinsi"
                                            value="{{ @$student->province ?? old('province') }}" />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="asrama_host_display">
                                            <span>Penanggung Jawab / Ustadz Kamar</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Penanggung Jawab / Ustadz Kamar otomatis tersinkron dari Data Asrama"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid bg-light" id="asrama_host_display"
                                            value="{{ @$student->asramaHost->name ?? (@$student->asrama?->hostAdmin?->name ?? '-') }}"
                                            readonly disabled />
                                        <span class="form-text text-muted fs-8">Otomatis terisi dari Data Asrama</span>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fs-6 fw-bold form-label" for="asrama_name_display">
                                            <span>Nama Kamar</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Nama Kamar Santri otomatis tersinkron dari Data Asrama"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid bg-light" id="asrama_name_display"
                                            value="{{ @$student->asrama_name ?? (@$student->asrama?->name ?? '-') }}"
                                            readonly disabled />
                                        <span class="form-text text-muted fs-8">Otomatis terisi dari Data Asrama</span>
                                    </div>
                                    
                                    <!-- Action Buttons moved to the third column -->
                                    <div class="d-flex justify-content-end mt-8">
                                        <a href="{{ route('student.index') }}"
                                            class="btn btn-light btn-active-light-primary me-2">Batal</a>
                                        <button type="submit" class="btn btn-primary"
                                            id="kt_account_profile_details_submit">Simpan</button>
                                    </div>
                                </div>
                            </div>
                            <!--end::Input group-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Card body-->
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
    // 3-Column Classroom Renderer Engine
    function render3ColClassrooms(classroomList, currentSelectedId) {
        var $grid = $('#classroom_3col_grid');
        $grid.empty();

        if (!classroomList || classroomList.length === 0) {
            $grid.html('<div class="col-12 text-center py-4 text-muted fs-7"><i class="fas fa-info-circle me-1"></i> Tidak ada kelas tersedia untuk sekolah ini</div>');
            $('#selected_class_name').text('Pilih Kelas');
            $('#classroom_id').val('');
            return;
        }

        // Group classrooms by Grade Prefix
        var gradeMap = {};
        $.each(classroomList, function(i, c) {
            var name = $.trim(c.name);
            var match = name.match(/^(VII|VIII|IX|X{1,2}I{0,2}|I{1,3}V?|[0-9]+)/i);
            var gradeKey = 'Lainnya';
            if (match) {
                var g = match[1].toUpperCase();
                if (g === '7' || g === 'VII') gradeKey = 'Kelas 7';
                else if (g === '8' || g === 'VIII') gradeKey = 'Kelas 8';
                else if (g === '9' || g === 'IX') gradeKey = 'Kelas 9';
                else if (g === '10' || g === 'X') gradeKey = 'Kelas 10 / X';
                else if (g === '11' || g === 'XI') gradeKey = 'Kelas 11 / XI';
                else if (g === '12' || g === 'XII') gradeKey = 'Kelas 12 / XII';
                else gradeKey = 'Tingkat ' + g;
            }
            if (!gradeMap[gradeKey]) gradeMap[gradeKey] = [];
            gradeMap[gradeKey].push(c);
        });

        var keys = Object.keys(gradeMap);
        var cols = [];

        if (keys.length === 3) {
            // Natural 3 grade levels (e.g. Kelas 7, 8, 9)
            cols = [
                { title: keys[0], items: gradeMap[keys[0]] },
                { title: keys[1], items: gradeMap[keys[1]] },
                { title: keys[2], items: gradeMap[keys[2]] }
            ];
        } else if (keys.length > 3) {
            // > 3 groups -> chunk into 3 balanced columns
            var total = classroomList.length;
            var chunkSize = Math.ceil(total / 3);
            cols = [
                { title: 'Tingkat I', items: classroomList.slice(0, chunkSize) },
                { title: 'Tingkat II', items: classroomList.slice(chunkSize, chunkSize * 2) },
                { title: 'Tingkat III', items: classroomList.slice(chunkSize * 2) }
            ];
        } else {
            // 1 or 2 groups -> chunk evenly into 3 columns
            var total = classroomList.length;
            var chunkSize = Math.ceil(total / 3);
            cols = [
                { title: 'Kolom 1', items: classroomList.slice(0, chunkSize) },
                { title: 'Kolom 2', items: classroomList.slice(chunkSize, chunkSize * 2) },
                { title: 'Kolom 3', items: classroomList.slice(chunkSize * 2) }
            ];
        }

        var selectedFound = false;

        // Render 3 Columns
        $.each(cols, function(colIdx, col) {
            if (!col.items || col.items.length === 0) return;

            var colHtml = '<div class="col-4">';
            colHtml += '  <div class="bg-white p-2 rounded-[12px] border border-gray-200 shadow-xs h-100">';
            colHtml += '    <div class="text-center fw-bolder text-primary fs-8 uppercase pb-1 mb-2 border-bottom border-gray-200">' + col.title + '</div>';
            colHtml += '    <div class="d-flex flex-column gap-1">';

            $.each(col.items, function(idx, item) {
                var isSelected = (String(item.id) === String(currentSelectedId));
                if (isSelected) selectedFound = true;

                colHtml += '      <button type="button" class="btn btn-sm btn-classroom-opt text-start d-flex align-items-center justify-content-between py-1.5 px-2 rounded-[8px] transition-all fs-8 fw-bold ' +
                    (isSelected ? 'btn-primary text-white shadow-xs' : 'btn-light-secondary text-gray-800 hover-elevate-up border border-gray-200') +
                    '" data-id="' + item.id + '" data-name="' + item.name + '">';
                colHtml += '        <span class="truncate">' + item.name + '</span>';
                colHtml += '        <i class="fas fa-check-circle fs-9 text-white ' + (isSelected ? '' : 'd-none') + '"></i>';
                colHtml += '      </button>';
            });

            colHtml += '    </div>';
            colHtml += '  </div>';
            colHtml += '</div>';

            $grid.append(colHtml);
        });

        // Update selected badge
        if (selectedFound) {
            var activeBtn = $grid.find('.btn-classroom-opt.btn-primary');
            if (activeBtn.length) {
                $('#selected_class_name').text(activeBtn.data('name'));
                $('#classroom_id').val(activeBtn.data('id'));
            }
        } else if (currentSelectedId) {
            $('#classroom_id').val(currentSelectedId);
        }
    }

    // Toggle 3-col Grid
    $('#btn_toggle_classroom').on('click', function(e) {
        e.preventDefault();
        $('#classroom_3col_card').toggleClass('d-none');
    });

    // Handle button click for single select
    $(document).off('click', '.btn-classroom-opt').on('click', '.btn-classroom-opt', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#classroom_id').val(id).trigger('change');
        $('#selected_class_name').text(name);

        $('#classroom_3col_grid .btn-classroom-opt')
            .removeClass('btn-primary text-white shadow-xs')
            .addClass('btn-light-secondary text-gray-800 hover-elevate-up border border-gray-200');
        $('#classroom_3col_grid .btn-classroom-opt i.fa-check-circle').addClass('d-none');

        $(this).removeClass('btn-light-secondary text-gray-800 border-gray-200')
            .addClass('btn-primary text-white shadow-xs');
        $(this).find('i.fa-check-circle').removeClass('d-none');
        
        // Hide the grid after selection
        $('#classroom_3col_card').addClass('d-none');
    });

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
                    render3ColClassrooms(data, $('#classroom_id').val());
                }
            });
        } else {
            render3ColClassrooms([], '');
        }
    });

    // Initialize immediately on load
    var initialClassrooms = @json($classrooms ?? []);
    var currentClassroomId = '{{ @$student->classroom_id }}';
    if (initialClassrooms && initialClassrooms.length > 0) {
        render3ColClassrooms(initialClassrooms, currentClassroomId);
    } else if ($('#school_id').val()) {
        $('#school_id').trigger('change');
    } 

    // Auto-fill Nama Panggilan from Nama Siswa (first name)
    const nicknameInput = document.getElementById('nickname');
    const nameInput = document.getElementById('name');
    if (nicknameInput && nameInput) {
        let nicknameManuallyTouched = nicknameInput.value.trim() !== '';
        nicknameInput.addEventListener('input', function() {
            nicknameManuallyTouched = nicknameInput.value.trim() !== '';
        });
        nameInput.addEventListener('input', function() {
            if (!nicknameManuallyTouched) {
                const fullName = nameInput.value.trim();
                if (fullName) {
                    const firstName = fullName.split(' ')[0];
                    nicknameInput.value = firstName;
                } else {
                    nicknameInput.value = '';
                }
            }
        });
    }
</script>
@endpush