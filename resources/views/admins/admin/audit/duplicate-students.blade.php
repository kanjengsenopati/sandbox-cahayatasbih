@extends('layouts.master', ['title' => 'Siswa Duplikat'])

@section('content')
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
                    <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Siswa Duplikat</h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="#" class="text-muted text-hover-primary">Audit dan Sinkron</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">
                            Siswa Duplikat
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">

                <!-- Session Alerts -->
                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center p-5 mb-6">
                        <span class="svg-icon svg-icon-2hx svg-icon-success me-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                <path d="M10.4343 12.4343L8.75 10.75C8.33579 10.3358 7.66421 10.3358 7.25 10.75C6.83579 11.1642 6.83579 11.8358 7.25 12.25L9.69289 14.6929C10.0834 15.0834 10.7166 15.0834 11.1071 14.6929L16.75 9.05C17.1642 8.63579 17.1642 7.96421 16.75 7.55C16.3358 7.13579 15.6642 7.13579 15.25 7.55L10.4343 12.4343Z" fill="currentColor"></path>
                            </svg>
                        </span>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Sukses</h4>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center p-5 mb-6">
                        <span class="svg-icon svg-icon-2hx svg-icon-danger me-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"></rect>
                                <rect x="11" y="14" width="2" height="2" rx="1" fill="currentColor"></rect>
                                <rect x="11" y="7" width="2" height="5" rx="1" fill="currentColor"></rect>
                            </svg>
                        </span>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Gagal</h4>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Duplicate Students Card -->
                <div class="card card-flush shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px; background: #ffffff;">
                    <div class="card-header border-0 pt-6 px-6 bg-transparent">
                        <div class="card-title flex-column">
                            <h3 class="card-label fw-bolder text-slate-800 fs-5" style="color: #1e293b;">Audit & Merge Siswa Duplikat</h3>
                            <span class="text-slate-400 fst-italic fs-7" style="color: #94a3b8;">
                                Mendeteksi siswa yang terduplikasi berdasarkan nama yang sama persis. Anda dapat menggabungkan data pembayaran, tagihan, dan saldo santri ke salah satu profil siswa target.
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-6 pt-2">
                        @if($duplicateStudents->isEmpty())
                            <div class="alert bg-light-success border border-success d-flex align-items-center p-5 rounded-[16px]" style="border-radius: 16px;">
                                <i class="fas fa-check-circle text-success fs-1 me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-dark">Data Bersih</h4>
                                    <span class="text-slate-600 fs-7">Tidak ditemukan siswa dengan nama duplikat di sistem.</span>
                                </div>
                            </div>
                        @else
                            <div class="alert bg-light-warning border border-warning d-flex align-items-center p-5 mb-6" style="border-radius: 16px; background-color: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2);">
                                <i class="fas fa-exclamation-triangle text-warning fs-1 me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-dark">Perhatian</h4>
                                    <span class="text-slate-600 fs-7" style="color: #475569;">Menghapus data siswa asal setelah merge bersifat permanen. Seluruh riwayat tagihan, transaksi, dan tabungan/saldo akan digabungkan ke siswa target.</span>
                                </div>
                            </div>

                            @foreach($duplicateStudents as $name => $students)
                                <div class="card border border-dashed border-gray-300 card-bordered mb-6 p-5" style="border-radius: 20px; background: #fafafb;">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="text-slate-800 fw-bold fs-6 mb-0" style="color: #1e293b;">
                                            <i class="fas fa-user-circle me-2 text-slate-400"></i> Kelompok Nama: <span class="text-primary fw-bolder">{{ $name }}</span>
                                        </h4>
                                        <span class="badge badge-light-danger fw-boldest px-3 py-1" style="color: #DC2626 !important;">{{ $students->count() }} Duplikat</span>
                                    </div>

                                    <form action="{{ route('admin.merge-students') }}" method="POST" class="form-merge-students">
                                        @csrf
                                        <div class="row g-4">
                                            @foreach($students as $index => $s)
                                                @php
                                                    $billCount = $s->bills()->count();
                                                    $classroomName = $s->classroom->name ?? '-';
                                                    $schoolName = $s->classroom->school->name ?? '-';
                                                    $schoolType = $s->classroom->school->type ?? '';
                                                @endphp
                                                <div class="col-md-6">
                                                    <div class="card bg-white p-5 h-100 shadow-[0_4px_20px_rgb(0,0,0,0.02)]" style="border-radius: 16px; border: 1px solid #e2e8f0;">
                                                        <div class="d-flex align-items-start gap-4">
                                                            <!-- Radio button selector -->
                                                            <div class="d-flex flex-column gap-3 align-items-center mt-1">
                                                                <div class="form-check form-check-custom form-check-solid">
                                                                    <input class="form-check-input radio-target" type="radio" name="target_id" value="{{ $s->id }}" required id="target_{{ $s->id }}">
                                                                    <label class="form-check-label text-emerald-600 fw-bold fs-7" for="target_{{ $s->id }}" style="color: #10B981;">
                                                                        Target
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-check-custom form-check-solid">
                                                                    <input class="form-check-input radio-source" type="radio" name="source_id" value="{{ $s->id }}" required id="source_{{ $s->id }}">
                                                                    <label class="form-check-label text-red-600 fw-bold fs-7" for="source_{{ $s->id }}" style="color: #DC2626;">
                                                                        Asal
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider" style="color: #94a3b8; font-size: 11px;">Siswa #{{ $index + 1 }}</span>
                                                                    <span class="badge badge-light-info fw-bold">{{ $schoolType }}</span>
                                                                </div>
                                                                
                                                                <table class="table table-borderless table-sm fs-7 mb-0">
                                                                    <tr>
                                                                        <td class="text-muted py-0" style="width: 35%">NIS</td>
                                                                        <td class="fw-bold py-0 text-slate-800">{{ $s->nis ?? '-' }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-muted py-0">UPT/Lembaga</td>
                                                                        <td class="fw-semibold py-0 text-slate-700">{{ $schoolName }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-muted py-0">Kelas</td>
                                                                        <td class="fw-semibold py-0 text-slate-700">{{ $classroomName }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-muted py-0">Wali Santri</td>
                                                                        <td class="fw-semibold py-0 text-slate-700">{{ $s->user->name ?? '-' }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-muted py-0">Total Saldo</td>
                                                                        <td class="fw-bold text-emerald-600 py-0" style="color: #10B981;">Rp {{ number_format($s->saldo, 0, ',', '.') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-muted py-0">Total Tabungan</td>
                                                                        <td class="fw-bold text-emerald-600 py-0" style="color: #10B981;">Rp {{ number_format($s->saving, 0, ',', '.') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-muted py-0">Tagihan</td>
                                                                        <td class="fw-semibold py-0"><span class="badge badge-light-warning">{{ $billCount }} Tagihan</span></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn text-white fw-bold px-6 py-3 btn-submit-merge" style="background-color: #2563EB; border-radius: 12px; border: none;">
                                                <i class="fas fa-compress-alt me-2 text-white"></i> Gabungkan Siswa (Merge)
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Saling silang pilihan source & target radio
            $('.radio-target').on('change', function() {
                var form = $(this).closest('form');
                var val = $(this).val();
                form.find('.radio-source[value="' + val + '"]').prop('checked', false);
                var otherSource = form.find('.radio-source').not('[value="' + val + '"]');
                if (otherSource.length === 1) {
                    otherSource.prop('checked', true);
                }
            });
            $('.radio-source').on('change', function() {
                var form = $(this).closest('form');
                var val = $(this).val();
                form.find('.radio-target[value="' + val + '"]').prop('checked', false);
                var otherTarget = form.find('.radio-target').not('[value="' + val + '"]');
                if (otherTarget.length === 1) {
                    otherTarget.prop('checked', true);
                }
            });

            // SweetAlert konfirmasi merge siswa
            $('.form-merge-students').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                
                var sourceRadio = $(form).find('.radio-source:checked');
                var targetRadio = $(form).find('.radio-target:checked');
                
                if (sourceRadio.length === 0 || targetRadio.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Silakan pilih profil ASAL dan TARGET siswa terlebih dahulu untuk digabungkan.'
                    });
                    return;
                }
                
                var sourceId = sourceRadio.val();
                var targetId = targetRadio.val();
                
                if (sourceId === targetId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan',
                        text: 'Profil ASAL dan TARGET tidak boleh sama!'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Merge Siswa',
                    text: 'Apakah Anda yakin ingin menggabungkan seluruh riwayat transaksi, tagihan, tabungan, dan saldo santri ini? Aksi ini akan menghapus permanen profil siswa asal.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563EB',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Gabungkan Sekarang!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses Merge...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
