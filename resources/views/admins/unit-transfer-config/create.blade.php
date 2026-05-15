@extends('layouts.master', ['title' => 'Tambah Setting Mutasi'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Tambah Setting</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">Lanjut Unit</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Tambah Setting</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('unit-transfer-config.index') }}" class="btn btn-sm btn-light">Kembali</a>
            </div>
        </div>
    </div>
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Form Tambah Jalur Lanjut Unit</h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    <form action="{{ route('unit-transfer-config.store') }}" method="POST">
                        @csrf
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-bold fs-6">Dari Unit Sekolah</label>
                            <div class="col-lg-8">
                                <select name="from_school_id" class="form-select form-select-solid" data-control="select2" required>
                                    <option value="">Pilih Sekolah Asal</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('from_school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                @error('from_school_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-bold fs-6">Ke Unit Sekolah Tujuan</label>
                            <div class="col-lg-8">
                                <select name="to_school_id" id="to_school_id" class="form-select form-select-solid" data-control="select2" required>
                                    <option value="">Pilih Sekolah Tujuan</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('to_school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                @error('to_school_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-bold fs-6">Otomatis Masuk Kelas</label>
                            <div class="col-lg-8">
                                <select name="to_classroom_id" id="to_classroom_id" class="form-select form-select-solid" data-control="select2" required>
                                    <option value="">Pilih Sekolah Tujuan Dulu</option>
                                </select>
                                <div class="form-text">Kelas penempatan sementara (contoh: "Kelas 10 Belum Ada Kelas").</div>
                                @error('to_classroom_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-bold fs-6">Tagihan Syarat Kenaikan (Daftar Ulang)</label>
                            <div class="col-lg-8">
                                <select name="bill_type_id" class="form-select form-select-solid" data-control="select2" required>
                                    <option value="">Pilih Jenis Tagihan</option>
                                    @foreach($billTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('bill_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Jika lunas, siswa otomatis dipindahkan ke sekolah & kelas tujuan.</div>
                                @error('bill_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-bold fs-6">Biaya Pendaftaran (Rp)</label>
                            <div class="col-lg-8">
                                <input type="number" name="amount" class="form-control form-control-solid" placeholder="Contoh: 500000" value="{{ old('amount', 0) }}" min="0" required />
                                <div class="form-text">Jumlah tagihan daftar ulang yang akan di-generate.</div>
                                @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Status Jalur</label>
                            <div class="col-lg-8 d-flex align-items-center">
                                <div class="form-check form-check-solid form-switch fv-row">
                                    <input class="form-check-input w-45px h-30px" type="checkbox" name="is_active" id="is_active" value="1" checked="checked" />
                                    <label class="form-check-label" for="is_active"></label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <button type="submit" class="btn btn-primary">Simpan Setting</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')
<script>
    $(document).ready(function() {
        $('#to_school_id').on('change', function() {
            var schoolId = $(this).val();
            var classSelect = $('#to_classroom_id');
            classSelect.empty().append('<option value="">Memuat kelas...</option>');
            
            if(schoolId) {
                $.get("{{ url('api/classrooms') }}/" + schoolId, function(data) {
                    classSelect.empty().append('<option value="">Pilih Kelas</option>');
                    $.each(data, function(index, classroom) {
                        classSelect.append('<option value="' + classroom.id + '">' + classroom.name + '</option>');
                    });
                });
            } else {
                classSelect.empty().append('<option value="">Pilih Sekolah Tujuan Dulu</option>');
            }
        });
    });
</script>
@endpush
