@extends('layouts.master', ['title' => 'Data Menu Aplikasi'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Menu Aplikasi</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('application-menu.index') }}">
                        <li class="text-muted">
                            Menu Aplikasi
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('application-menu.create') ? 'Tambah Menu Aplikasi' : 'Edit Menu Aplikasi'
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
            <div class="row g-7">
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <div class="card-body pt-5">
                            <x-alert.alert-validation />
                            <form id="application-menu"
                                action="{{ request()->routeIs('application-menu.create') ? route('application-menu.store') : route('application-menu.update', @$applicationMenu->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />

                                <!--begin::Nama Menu-->
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Menu Aplikasi</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Menu Aplikasi Yang Valid"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="name" id="name"
                                        placeholder="Masukkan Menu Aplikasi"
                                        value="{{ @$applicationMenu->name ?? old('name') }}" required />
                                </div>

                                <!--begin::Tipe Menu-->
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Tipe Menu / Jenis Tautan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih apakah menu ini membuka fitur internal aplikasi atau link WhatsApp/eksternal"></i>
                                    </label>
                                    @php
                                        $currentType = old('type', @$applicationMenu->type ?? 'internal');
                                    @endphp
                                    <div class="row g-4 mt-1">
                                        <div class="col-md-4">
                                            <label class="btn btn-outline btn-outline-dashed btn-outline-default d-flex text-start p-4 h-100 {{ $currentType === 'internal' ? 'active' : '' }}" for="type_internal">
                                                <span class="form-check form-check-custom form-check-solid form-check-primary me-4">
                                                    <input class="form-check-input menu-type-radio" type="radio" name="type" id="type_internal" value="internal" {{ $currentType === 'internal' ? 'checked' : '' }} />
                                                </span>
                                                <span class="d-flex flex-column">
                                                    <span class="fw-bolder text-gray-800 fs-6">Fitur Internal PWA</span>
                                                    <span class="fs-7 text-muted">Membuka rute/fitur bawaan di aplikasi</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="btn btn-outline btn-outline-dashed btn-outline-default d-flex text-start p-4 h-100 {{ $currentType === 'whatsapp' ? 'active' : '' }}" for="type_whatsapp">
                                                <span class="form-check form-check-custom form-check-solid form-check-success me-4">
                                                    <input class="form-check-input menu-type-radio" type="radio" name="type" id="type_whatsapp" value="whatsapp" {{ $currentType === 'whatsapp' ? 'checked' : '' }} />
                                                </span>
                                                <span class="d-flex flex-column">
                                                    <span class="fw-bolder text-gray-800 fs-6"><i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Petugas</span>
                                                    <span class="fs-7 text-muted">Chat wa.me dengan prefilled pesan dinamis</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="btn btn-outline btn-outline-dashed btn-outline-default d-flex text-start p-4 h-100 {{ $currentType === 'external' ? 'active' : '' }}" for="type_external">
                                                <span class="form-check form-check-custom form-check-solid form-check-primary me-4">
                                                    <input class="form-check-input menu-type-radio" type="radio" name="type" id="type_external" value="external" {{ $currentType === 'external' ? 'checked' : '' }} />
                                                </span>
                                                <span class="d-flex flex-column">
                                                    <span class="fw-bolder text-gray-800 fs-6"><i class="fas fa-external-link-alt text-primary me-1"></i> URL Eksternal Bebas</span>
                                                    <span class="fs-7 text-muted">Membuka link web / formulir luar</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Tipe Menu-->

                                <!--begin::Section WhatsApp-->
                                <div id="section_whatsapp" class="bg-light-success border border-success border-dashed rounded-3 p-5 mb-7" style="display: none;">
                                    <h5 class="text-success fw-bolder mb-4">
                                        <i class="fab fa-whatsapp me-2 text-success fs-4"></i>Konfigurasi WhatsApp Petugas
                                    </h5>
                                    
                                    <div class="fv-row mb-5">
                                        <label class="fs-6 fw-bold form-label" for="wa_number">
                                            <span class="required">Nomor WhatsApp Petugas</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Contoh: 081234567890 atau 6281234567890"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fab fa-whatsapp text-success fs-5"></i></span>
                                            <input type="text" class="form-control" name="wa_number" id="wa_number"
                                                placeholder="Contoh: 081234567890"
                                                value="{{ @$applicationMenu->wa_number ?? old('wa_number') }}" />
                                        </div>
                                        <span class="text-muted fs-7 mt-1 d-block">Nomor dengan awalan <code>08...</code> otomatis dinormalisasi ke format internasional <code>628...</code>.</span>
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold form-label" for="wa_message">
                                            <span>Template Pesan WhatsApp (Prefilled Custom Message)</span>
                                        </label>
                                        <textarea class="form-control" name="wa_message" id="wa_message" rows="3"
                                            placeholder="Masukkan template pesan...">{{ @$applicationMenu->wa_message ?? old('wa_message', "Assalamu'alaikum, saya wali santri [nama_wali], orang tua / wali dari [nama-santri dan kelas] ingin bertanya") }}</textarea>
                                        
                                        <div class="mt-2.5">
                                            <span class="text-gray-700 fs-7 fw-bold me-2">Sisipkan Variabel Dinamis (Klik untuk menyisipkan):</span>
                                            <div class="d-flex flex-wrap gap-2 mt-1.5">
                                                <button type="button" class="btn btn-xs btn-light-primary insert-token" data-token="[nama_wali]">
                                                    + [nama_wali]
                                                </button>
                                                <button type="button" class="btn btn-xs btn-light-success insert-token" data-token="[nama-santri dan kelas]">
                                                    + [nama-santri dan kelas]
                                                </button>
                                                <button type="button" class="btn btn-xs btn-light-info insert-token" data-token="[nama_santri]">
                                                    + [nama_santri]
                                                </button>
                                                <button type="button" class="btn btn-xs btn-light-warning insert-token" data-token="[kelas]">
                                                    + [kelas]
                                                </button>
                                                <button type="button" class="btn btn-xs btn-light-dark insert-token" data-token="[unit_sekolah]">
                                                    + [unit_sekolah]
                                                </button>
                                            </div>
                                            <span class="text-muted fs-8 mt-1 d-block">Variabel di atas akan diisi otomatis dari profil akun wali dan data santri aktif saat tombol diklik di PWA.</span>
                                        </div>
                                    </div>

                                    <!-- Live Preview -->
                                    <div class="bg-white rounded p-3 border border-gray-200 mt-4">
                                        <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1"><i class="fas fa-eye me-1"></i> Live Preview Pesan WhatsApp:</span>
                                        <p class="fs-7 text-gray-800 mb-0 font-monospace" id="wa_preview_text">
                                            "Assalamu'alaikum, saya wali santri <strong>Bapak Ahmad</strong>, orang tua / wali dari <strong>Muhammad Zaid (VII-A)</strong> ingin bertanya"
                                        </p>
                                    </div>
                                </div>
                                <!--end::Section WhatsApp-->

                                <!--begin::Section External URL-->
                                <div id="section_external" class="bg-light-primary border border-primary border-dashed rounded-3 p-5 mb-7" style="display: none;">
                                    <h5 class="text-primary fw-bolder mb-4">
                                        <i class="fas fa-external-link-alt me-2 text-primary fs-5"></i>Konfigurasi URL Eksternal
                                    </h5>
                                    
                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold form-label" for="url">
                                            <span class="required">URL Tujuan</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-link text-primary fs-5"></i></span>
                                            <input type="url" class="form-control" name="url" id="url"
                                                placeholder="https://contoh-link.com/formulir"
                                                value="{{ @$applicationMenu->url ?? old('url') }}" />
                                        </div>
                                        <span class="text-muted fs-7 mt-1 d-block">Mendukung token dinamis seperti <code>[nama_wali]</code>, <code>[nama_santri]</code>, dan <code>[kelas]</code>.</span>
                                    </div>
                                </div>
                                <!--end::Section External URL-->

                                <!--begin::Feature Flag-->
                                <div class="fv-row mb-7" id="section_flag">
                                    <label class="fs-6 fw-bold form-label mt-3" for="flag">
                                        <span class="required">Feature Flag</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Feature Flag Yang Valid"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="flag" id="flag"
                                        placeholder="Masukkan Feature Flag"
                                        value="{{ @$applicationMenu->flag ?? old('flag') }}" />
                                    <span class="text-muted fs-7 mt-1 d-block">Digunakan untuk mencocokkan rute internal PWA (contoh: <code>tahfidz</code>, <code>nilai</code>, <code>petugas</code>). Untuk WhatsApp / link eksternal, flag akan otomatis dibuat jika dikosongkan.</span>
                                </div>

                                <!--begin::Scope Visibility-->
                                <div class="separator my-6"></div>
                                <h4 class="fw-bolder text-dark mb-4">
                                    <i class="fas fa-filter me-2 text-primary"></i>Pengaturan Visibilitas Menu
                                </h4>

                                <div class="fv-row mb-5">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="enable_scope" id="enable_scope" value="1"
                                            {{ (isset($applicationMenu) && $applicationMenu->scopes->isNotEmpty()) ? 'checked' : '' }} />
                                        <label class="form-check-label fw-bold text-gray-700" for="enable_scope">
                                            Tampilkan hanya untuk Unit Pendidikan / Jenjang Kelas tertentu
                                        </label>
                                    </div>
                                    <div class="text-muted fs-7 mt-1">
                                        Jika tidak dicentang, menu akan tampil untuk <strong>semua santri</strong> (global).
                                    </div>
                                </div>

                                <div id="scope-section" style="display: none;">
                                    <!--begin::Unit Pendidikan-->
                                    <div class="fv-row mb-5">
                                        <label class="fs-6 fw-bold form-label mt-3">
                                            <span class="required">Unit Pendidikan</span>
                                        </label>
                                        <select class="form-select form-select-solid" name="scope_schools[]" id="scope_schools"
                                            data-control="select2" data-placeholder="Pilih Unit Pendidikan" multiple>
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}"
                                                    {{ (isset($applicationMenu) && $applicationMenu->scopes->pluck('school_id')->contains($school->id)) ? 'selected' : '' }}>
                                                    {{ $school->name }} ({{ $school->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!--begin::Jenjang Kelas-->
                                    <div class="fv-row mb-5">
                                        <label class="fs-6 fw-bold form-label mt-3">
                                            Jenjang Kelas
                                            <span class="text-muted fw-normal fs-7">(Opsional — kosongkan untuk semua jenjang di unit terpilih)</span>
                                        </label>
                                        <select class="form-select form-select-solid" name="scope_class_levels[]" id="scope_class_levels"
                                            data-control="select2" data-placeholder="Pilih Jenjang Kelas (opsional)" multiple>
                                            {{-- Akan diisi via AJAX berdasarkan unit terpilih --}}
                                        </select>
                                    </div>
                                </div>
                                <!--end::Scope Visibility-->

                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('application-menu.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                                <!--end::Action buttons-->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->
@endsection

@push('js')
<script>
$(document).ready(function() {
    const $enableScope = $('#enable_scope');
    const $scopeSection = $('#scope-section');
    const $scopeSchools = $('#scope_schools');
    const $scopeClassLevels = $('#scope_class_levels');

    // Menu type elements
    const $typeRadios = $('.menu-type-radio');
    const $sectionFlag = $('#section_flag');
    const $sectionWhatsapp = $('#section_whatsapp');
    const $sectionExternal = $('#section_external');
    const $flagInput = $('#flag');
    const $waNumber = $('#wa_number');
    const $waMessage = $('#wa_message');
    const $urlInput = $('#url');
    const $waPreview = $('#wa_preview_text');

    function syncMenuType() {
        const selectedType = $('input[name="type"]:checked').val() || 'internal';
        
        // Update label active class
        $typeRadios.each(function() {
            $(this).closest('label').toggleClass('active', $(this).is(':checked'));
        });

        if (selectedType === 'whatsapp') {
            $sectionWhatsapp.slideDown(200);
            $sectionExternal.slideUp(200);
            $sectionFlag.slideUp(200);
            $flagInput.prop('required', false);
            $waNumber.prop('required', true);
            $urlInput.prop('required', false);
        } else if (selectedType === 'external') {
            $sectionWhatsapp.slideUp(200);
            $sectionExternal.slideDown(200);
            $sectionFlag.slideUp(200);
            $flagInput.prop('required', false);
            $waNumber.prop('required', false);
            $urlInput.prop('required', true);
        } else {
            $sectionWhatsapp.slideUp(200);
            $sectionExternal.slideUp(200);
            $sectionFlag.slideDown(200);
            $flagInput.prop('required', true);
            $waNumber.prop('required', false);
            $urlInput.prop('required', false);
        }
        updateWaPreview();
    }

    $typeRadios.on('change', syncMenuType);
    syncMenuType();

    // Insert dynamic token to cursor position
    $('.insert-token').on('click', function(e) {
        e.preventDefault();
        const token = $(this).data('token');
        const textarea = $waMessage[0];
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + token + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + token.length;
        textarea.focus();
        updateWaPreview();
    });

    // Update Live Preview
    function updateWaPreview() {
        const text = $waMessage.val() || "";
        let preview = text
            .replace(/\[nama[-_]wali\]|\{nama[-_]wali\}/gi, '<span class="badge badge-light-primary fw-bolder">Bapak Ahmad (Nama Wali)</span>')
            .replace(/\[nama[-_]santri dan kelas\]|\[nama[-_]siswa dan kelas\]|\{nama[-_]santri dan kelas\}/gi, '<span class="badge badge-light-success fw-bolder">Muhammad Zaid - Kelas VII-A</span>')
            .replace(/\[nama[-_]santri\]|\[nama[-_]siswa\]|\{nama[-_]santri\}|\{nama[-_]siswa\}/gi, '<span class="badge badge-light-info fw-bolder">Muhammad Zaid</span>')
            .replace(/\[kelas\]|\{kelas\}/gi, '<span class="badge badge-light-warning fw-bolder">VII-A</span>')
            .replace(/\[unit[-_]?sekolah\]|\[sekolah\]|\{unit[-_]?sekolah\}|\{sekolah\}/gi, '<span class="badge badge-light-dark fw-bolder">SMP Cahaya Tasbih</span>');

        $waPreview.html(preview || '<span class="text-muted fst-italic">Pesan belum diisi...</span>');
    }

    $waMessage.on('input', updateWaPreview);
    updateWaPreview();

    // Existing class levels dari database (saat edit)
    const existingClassLevels = @json(isset($applicationMenu) ? $applicationMenu->scopes->pluck('class_level')->filter()->unique()->values() : []);

    // Toggle scope section
    function toggleScope() {
        if ($enableScope.is(':checked')) {
            $scopeSection.slideDown(200);
        } else {
            $scopeSection.slideUp(200);
        }
    }
    toggleScope();
    $enableScope.on('change', toggleScope);

    // Fetch class levels ketika unit pendidikan berubah
    function loadClassLevels() {
        const schoolIds = $scopeSchools.val();
        if (!schoolIds || schoolIds.length === 0) {
            $scopeClassLevels.empty().trigger('change');
            return;
        }

        $.ajax({
            url: '{{ route("application-menu.get-class-levels") }}',
            data: { school_ids: schoolIds },
            success: function(levels) {
                const currentVal = $scopeClassLevels.val() || [];
                $scopeClassLevels.empty();
                levels.forEach(function(level) {
                    const selected = currentVal.includes(level) || existingClassLevels.includes(level);
                    $scopeClassLevels.append(new Option('Kelas ' + level, level, selected, selected));
                });
                $scopeClassLevels.trigger('change');
            }
        });
    }

    $scopeSchools.on('change', loadClassLevels);

    // Initial load saat halaman edit
    if ($scopeSchools.val() && $scopeSchools.val().length > 0) {
        loadClassLevels();
    }
});
</script>
@endpush