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

                                <!--begin::Feature Flag-->
                                <div class="fv-row mb-7">
                                    <label class="fs-6 fw-bold form-label mt-3" for="flag">
                                        <span class="required">Feature Flag</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Feature Flag Yang Valid"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" name="flag" id="flag"
                                        placeholder="Masukkan Feature Flag"
                                        value="{{ @$applicationMenu->flag ?? old('flag') }}" required />
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