@extends('layouts.master', ['title' => 'Detail Gelombang PPDB'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-xxl d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">{{ $ppdbWaves->name }}</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('ppdb-waves.index') }}" class="text-muted text-hover-primary">Gelombang PPDB</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Detail</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('ppdb-waves.edit', $ppdbWaves->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit fs-2"></i>Edit
                </a>
                <a href="{{ route('ppdb-waves.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left fs-2"></i>Kembali
                </a>
            </div>
            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Info Card-->
            <div class="row mb-6">
                <div class="col-md-3">
                    <div class="card border-0 h-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <span class="text-gray-400 fw-semibold fs-6 mb-2">Tahun Akademik</span>
                            <span class="text-dark fw-bolder fs-5">{{ $ppdbWaves->academicYear->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 h-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <span class="text-gray-400 fw-semibold fs-6 mb-2">Tanggal Mulai</span>
                            <span class="text-dark fw-bolder fs-5">{{ date('d M Y', strtotime($ppdbWaves->start_date)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 h-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <span class="text-gray-400 fw-semibold fs-6 mb-2">Tanggal Berakhir</span>
                            <span class="text-dark fw-bolder fs-5">{{ date('d M Y', strtotime($ppdbWaves->end_date)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 h-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <span class="text-gray-400 fw-semibold fs-6 mb-2">Status</span>
                            @if ($ppdbWaves->is_active)
                                <span class="badge badge-success w-fit">Aktif</span>
                            @else
                                <span class="badge badge-secondary w-fit">Non Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Info Card-->

            <!--begin::Success/Error Messages-->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-content">
                        <strong>Terjadi Kesalahan!</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <!--end::Success/Error Messages-->

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h3 class="text-dark">Data Track Pendaftaran</h3>
                    </div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddTrack">
                        <i class="fas fa-plus fs-2"></i>Tambah Track
                    </button>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    @if ($ppdbTracks->isEmpty())
                        <div class="text-center py-10">
                            <i class="fas fa-inbox fs-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada data track pendaftaran</p>
                        </div>
                    @else
                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th>Sekolah</th>
                                        <th>Tipe Pendaftaran</th>
                                        <th>Biaya Daftar Ulang</th>
                                        <th>Kuota</th>
                                        <th>Status</th>
                                        <th>Alasan Penutupan</th>
                                        <th class="text-center min-w-100px" style="width: 15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-bold">
                                    @foreach ($ppdbTracks as $track)
                                        <tr>
                                            <td class="text-dark fw-bold">{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="text-dark fw-bold">{{ $track->school->name ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $track->registration_type }}</span>
                                            </td>
                                            <td>
                                                <span class="text-dark">Rp {{ number_format($track->registration_fee, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-primary">{{ $track->quota }}</span>
                                            </td>
                                            <td>
                                                @if ($track->is_open)
                                                    <span class="badge badge-success">
                                                    Buka
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                    Tutup
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    @if ($track->close_reason)
                                                        {{ Str::limit($track->close_reason, 30) }}
                                                    @else
                                                        -
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-icon btn-sm btn-info btn-active-light" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditTrack"
                                                        onclick="editTrack('{{ $track->id }}')"
                                                        title="Edit">
                                                    <i class="fas fa-edit fs-2"></i>
                                                </button>
                                                <form action="{{ route('ppdb-track.destroy', $track->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-sm btn-danger btn-active-light" 
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                                            title="Hapus">
                                                        <i class="fas fa-trash fs-2"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                    @endif
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>


{{-- Modal Tambah Track --}}
<div class="modal fade" id="modalAddTrack" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder">Tambah Track Pendaftaran</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ppdb-track.store') }}" method="POST" id="formAddTrack">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Sekolah <span class="text-danger">*</span></label>
                                <select class="form-select form-select-solid" id="school_id" name="school_id" required>
                                    <option value="">-- Pilih Sekolah --</option>
                                    @foreach ($schools ?? \App\Models\School::get() as $school)
                                        <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Tipe Pendaftaran <span class="text-danger">*</span></label>
                                <select class="form-select form-select-solid" id="registration_type" name="registration_type" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    @php $ppdbTrack = new \App\Models\PpdbTrack(); $types = $ppdbTrack->getListRegistrationTypes(); @endphp
                                    @foreach ($types as $typeKey => $typeLabel)
                                        <option value="{{ $typeKey }}" @selected(old('registration_type') == $typeKey)>
                                            {{ $typeLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="ppdb_wave_id" value="{{ $ppdbWaves->id }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Biaya Daftar Ulang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-solid" 
                                       id="registration_fee" placeholder="0" value="{{ old('registration_fee') ? number_format(old('registration_fee'), 0, ',', '.') : '' }}" required
                                       oninput="formatCurrency(this)">
                                <input type="hidden" name="registration_fee" id="registration_fee_hidden" value="{{ old('registration_fee') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Kuota <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-solid" 
                                       id="quota" name="quota" min="1" placeholder="0"
                                       value="{{ old('quota') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="fv-row mb-5">
                        <label class="form-label fw-bolder text-dark fs-6 mb-2">Link WhatsApp Group</label>
                        <input type="url" class="form-control form-control-solid" 
                               id="link_whatsapp_group" name="link_whatsapp_group" 
                               placeholder="https://chat.whatsapp.com/..." 
                               value="{{ old('link_whatsapp_group') }}">
                    </div>

                    <div class="fv-row mb-5">
                        <label class="form-label fw-bolder text-dark fs-6 mb-2">Status <span class="text-danger">*</span></label>
                        <select class="form-select form-select-solid" id="is_open_add" name="is_open" required onchange="toggleCloseReasonAdd()">
                            <option value="">-- Pilih Status --</option>
                            <option value="1" @selected(old('is_open') == '1')>Buka</option>
                            <option value="0" @selected(old('is_open') == '0')>Tutup</option>
                        </select>
                    </div>

                    <div class="fv-row mb-5" id="close_reason_container_add" style="display: none;">
                        <label class="form-label fw-bolder text-dark fs-6 mb-2">Alasan Penutupan</label>
                        <textarea class="form-control form-control-solid" id="close_reason_add" name="close_reason" 
                                  placeholder="Tulis alasan penutupan..." rows="2">{{ old('close_reason') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer flex-center pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Track --}}
<div class="modal fade" id="modalEditTrack" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder">Edit Track Pendaftaran</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditTrack" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Sekolah <span class="text-danger">*</span></label>
                                <select class="form-select form-select-solid" id="edit_school_id" name="school_id" required>
                                    <option value="">-- Pilih Sekolah --</option>
                                    @foreach ($schools ?? \App\Models\School::get() as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Tipe Pendaftaran <span class="text-danger">*</span></label>
                                <select class="form-select form-select-solid" id="edit_registration_type" name="registration_type" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    @php $ppdbTrack = new \App\Models\PpdbTrack(); $types = $ppdbTrack->getListRegistrationTypes(); @endphp
                                    @foreach ($types as $typeKey => $typeLabel)
                                        <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="edit_ppdb_wave_id" name="ppdb_wave_id" value="{{ $ppdbWaves->id }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Biaya Daftar Ulang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-solid" 
                                       id="edit_registration_fee" placeholder="0" required
                                       oninput="formatCurrency(this)">
                                <input type="hidden" name="registration_fee" id="edit_registration_fee_hidden">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row mb-5">
                                <label class="form-label fw-bolder text-dark fs-6 mb-2">Kuota <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-solid" id="edit_quota" name="quota" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="fv-row mb-5">
                        <label class="form-label fw-bolder text-dark fs-6 mb-2">Link WhatsApp Group</label>
                        <input type="url" class="form-control form-control-solid" 
                               id="edit_link_whatsapp_group" name="link_whatsapp_group" 
                               placeholder="https://chat.whatsapp.com/...">
                    </div>

                    <div class="fv-row mb-5">
                        <label class="form-label fw-bolder text-dark fs-6 mb-2">Status <span class="text-danger">*</span></label>
                        <select class="form-select form-select-solid" id="edit_is_open" name="is_open" required onchange="toggleCloseReasonEdit()">
                            <option value="">-- Pilih Status --</option>
                            <option value="1">Buka</option>
                            <option value="0">Tutup</option>
                        </select>
                    </div>

                    <div class="fv-row mb-5" id="close_reason_container_edit" style="display: none;">
                        <label class="form-label fw-bolder text-dark fs-6 mb-2">Alasan Penutupan</label>
                        <textarea class="form-control form-control-solid" id="edit_close_reason" name="close_reason" 
                                  placeholder="Tulis alasan penutupan..." rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer flex-center pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    // Format currency with thousand separator
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') {
            input.value = '';
            const hiddenFieldId = input.id + '_hidden';
            const hiddenField = document.getElementById(hiddenFieldId);
            if (hiddenField) {
                hiddenField.value = '';
            }
            return;
        }
        let formatted = new Intl.NumberFormat('id-ID').format(value);
        input.value = formatted;
        
        // Update hidden field with actual value
        const hiddenFieldId = input.id + '_hidden';
        const hiddenField = document.getElementById(hiddenFieldId);
        if (hiddenField) {
            hiddenField.value = value;
        }
    }

    // Toggle close reason visibility for add modal
    function toggleCloseReasonAdd() {
        const closeReasonContainer = document.getElementById('close_reason_container_add');
        const isOpen = document.getElementById('is_open_add').value;
        if (isOpen === '0') {
            closeReasonContainer.style.display = 'block';
        } else {
            closeReasonContainer.style.display = 'none';
        }
    }

    // Toggle close reason visibility for edit modal
    function toggleCloseReasonEdit() {
        const closeReasonContainer = document.getElementById('close_reason_container_edit');
        const isOpen = document.getElementById('edit_is_open').value;
        if (isOpen === '0') {
            closeReasonContainer.style.display = 'block';
        } else {
            closeReasonContainer.style.display = 'none';
        }
    }

    // Edit track function
    function editTrack(trackId) {
        fetch('/ppdb-track/' + trackId + '/edit')
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_school_id').value = data.school_id;
                document.getElementById('edit_registration_type').value = data.registration_type;
                
                // Format registration fee
                const formattedFee = new Intl.NumberFormat('id-ID').format(data.registration_fee);
                document.getElementById('edit_registration_fee').value = formattedFee;
                document.getElementById('edit_registration_fee_hidden').value = data.registration_fee;
                
                document.getElementById('edit_quota').value = data.quota;
                document.getElementById('edit_link_whatsapp_group').value = data.link_whatsapp_group || '';
                document.getElementById('edit_is_open').value = data.is_open ? '1' : '0';
                document.getElementById('edit_close_reason').value = data.close_reason || '';

                // Set form action
                document.getElementById('formEditTrack').action = '/ppdb-track/' + trackId;

                // Toggle close reason visibility
                toggleCloseReasonEdit();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat data');
            });
    }

    // Setup event listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const formAdd = document.getElementById('formAddTrack');
        if (formAdd) {
            formAdd.addEventListener('submit', function(e) {
                const registrationFee = document.getElementById('registration_fee').value.replace(/\D/g, '');
                document.getElementById('registration_fee_hidden').value = registrationFee;
            });
        }

        // Setup form submission for edit modal
        const formEdit = document.getElementById('formEditTrack');
        if (formEdit) {
            formEdit.addEventListener('submit', function(e) {
                const registrationFee = document.getElementById('edit_registration_fee').value.replace(/\D/g, '');
                document.getElementById('edit_registration_fee_hidden').value = registrationFee;
            });
        }
    });
</script>
@endpush

@endsection
