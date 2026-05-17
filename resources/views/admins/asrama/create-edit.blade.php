@extends('layouts.master', ['title' => isset($asrama) ? 'Edit Data Asrama' : 'Tambah Data Asrama'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">{{ isset($asrama) ? 'Edit Data Asrama' : 'Tambah Data Asrama' }}</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('asrama.index') }}" class="text-muted text-hover-primary">Data Asrama</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">{{ isset($asrama) ? 'Edit Asrama' : 'Tambah Asrama' }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Formulir {{ isset($asrama) ? 'Edit' : 'Tambah' }} Data Asrama</h2>
                    </div>
                </div>
                <!--end::Card header-->
                
                <!--begin::Card body-->
                <div class="card-body">
                    <form action="{{ isset($asrama) ? route('asrama.update', $asrama->id) : route('asrama.store') }}" method="POST" id="form-asrama">
                        @csrf
                        @if(isset($asrama))
                            @method('PUT')
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger p-5 mb-8">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!--begin::Input Group - Nama Asrama-->
                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Nama Asrama</label>
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" 
                                placeholder="Contoh: Asrama Al-Fatih, Asrama Umar bin Khattab" 
                                value="{{ old('name', isset($asrama) ? $asrama->name : '') }}" required />
                        </div>
                        <!--end::Input Group-->

                        <!--begin::Divider-->
                        <div class="separator separator-dashed my-8"></div>
                        <!--end::Divider-->

                        <!--begin::Host Section-->
                        <h4 class="fw-bolder mb-6"><i class="fa fa-user-tie text-primary me-2"></i>Pengaturan Ustadz / Host Pembina</h4>
                        
                        <!--begin::Host Type Radio Selection-->
                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-3">Tipe Input Ustadz Host</label>
                            <div class="d-flex align-items-center gap-10 mt-2">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" value="existing" name="host_type" id="host_existing" 
                                        {{ old('host_type', isset($currentOfficerId) ? 'existing' : 'existing') === 'existing' ? 'checked' : '' }} />
                                    <label class="form-check-label fw-bold text-gray-800" for="host_existing">
                                        Pilih Dari Data Petugas Aktif
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" value="new" name="host_type" id="host_new"
                                        {{ old('host_type') === 'new' ? 'checked' : '' }} />
                                    <label class="form-check-label fw-bold text-gray-800" for="host_new">
                                        + Buat / Entry Petugas Baru
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!--begin::Existing Host Select Group-->
                        <div class="fv-row mb-7" id="existing_host_group">
                            <label class="required fw-bold fs-6 mb-2">Pilih Ustadz / Pembina</label>
                            <select name="officer_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Petugas Pembina...">
                                <option value=""></option>
                                @foreach($officers as $officer)
                                    <option value="{{ $officer->id }}" 
                                        {{ old('officer_id', isset($currentOfficerId) ? $currentOfficerId : '') == $officer->id ? 'selected' : '' }}>
                                        {{ $officer->name }} (WA: {{ $officer->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-7 mt-2">Akun Admin PWA Asatidz akan secara otomatis disinkronkan ke nomor WA petugas ini.</div>
                        </div>

                        <!--begin::New Host Form Group (Hidden by default)-->
                        <div id="new_host_group" class="d-none bg-light-primary p-6 rounded-3 mb-7 border border-primary border-dashed">
                            <h5 class="fw-bold mb-5"><i class="fa fa-plus-circle text-primary me-2"></i>Formulir Input Petugas & Akun Admin Baru</h5>
                            
                            <div class="row g-9 mb-6">
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-bold fs-6 mb-2">Nama Lengkap Petugas</label>
                                    <input type="text" name="new_officer_name" class="form-control form-control-solid" 
                                        placeholder="Nama Lengkap Ustadz/Ustadzah" value="{{ old('new_officer_name') }}" />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-bold fs-6 mb-2">No. WhatsApp Petugas (Format: 08...)</label>
                                    <input type="text" name="new_officer_phone" class="form-control form-control-solid" 
                                        placeholder="Contoh: 081225129109" value="{{ old('new_officer_phone') }}" />
                                </div>
                            </div>

                            <div class="row g-9">
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-bold fs-6 mb-2">Jabatan</label>
                                    <input type="text" name="new_officer_position" class="form-control form-control-solid" 
                                        placeholder="Contoh: Host Asrama, Pembina Kamar" value="{{ old('new_officer_position', 'Host Asrama') }}" />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-bold fs-6 mb-2">Tugas / Tanggung Jawab</label>
                                    <input type="text" name="new_officer_duty" class="form-control form-control-solid" 
                                        placeholder="Contoh: Pengawasan & Perizinan Asrama" value="{{ old('new_officer_duty', 'Pengawasan & Perizinan Asrama') }}" />
                                </div>
                            </div>
                            <div class="text-primary fs-7 mt-4">
                                <i class="fa fa-info-circle text-primary me-1"></i> Sistem akan secara otomatis membuatkan akun login PWA Asatidz dengan password default: <strong>12345678</strong>.
                            </div>
                        </div>
                        <!--end::New Host Form Group-->

                        <!--begin::Divider-->
                        <div class="separator separator-dashed my-8"></div>
                        <!--end::Divider-->

                        <!--begin::Students Section-->
                        <h4 class="fw-bolder mb-6"><i class="fa fa-graduation-cap text-success me-2"></i>Alokasi Siswa / Santri Binaan</h4>

                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-2">Pilih Santri yang Tinggal di Asrama Ini</label>
                            <select name="student_ids[]" class="form-select form-select-solid" data-control="select2" 
                                data-placeholder="Pilih & alokasikan santri..." data-allow-clear="true" multiple="multiple">
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" 
                                        {{ (isset($assignedStudentIds) && in_array($student->id, $assignedStudentIds)) || (is_array(old('student_ids')) && in_array($student->id, old('student_ids'))) ? 'selected' : '' }}>
                                        {{ $student->name }} (NISN: {{ $student->nisn ?? '-' }} | Kelas: {{ $student->classroom ? $student->classroom->name : 'Tanpa Kelas' }})
                                        @if($student->asrama_name && (!isset($asrama) || $student->asrama_id !== $asrama->id))
                                            [Sudah di {{ $student->asrama_name }}]
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-7 mt-2">Daftar di atas menampilkan semua siswa aktif. Siswa yang dialokasikan akan berpindah asrama ke asrama ini.</div>
                        </div>

                        <!--begin::Actions-->
                        <div class="text-end pt-10">
                            <a href="{{ route('asrama.index') }}" class="btn btn-light me-3">Batal</a>
                            <button type="submit" class="btn btn-primary" id="btn-save">
                                <span class="indicator-label"><i class="fa fa-save me-1"></i> Simpan Data</span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        function toggleHostGroups() {
            const hostType = $('input[name="host_type"]:checked').val();
            if (hostType === 'existing') {
                $('#existing_host_group').removeClass('d-none');
                $('#new_host_group').addClass('d-none');
                $('select[name="officer_id"]').attr('required', true);
                $('input[name="new_officer_name"]').attr('required', false);
                $('input[name="new_officer_phone"]').attr('required', false);
            } else {
                $('#existing_host_group').addClass('d-none');
                $('#new_host_group').removeClass('d-none');
                $('select[name="officer_id"]').attr('required', false);
                $('input[name="new_officer_name"]').attr('required', true);
                $('input[name="new_officer_phone"]').attr('required', true);
            }
        }

        // Trigger on load
        toggleHostGroups();

        // Trigger on radio change
        $('input[name="host_type"]').change(() => {
            toggleHostGroups();
        });
    });
</script>
@endpush
