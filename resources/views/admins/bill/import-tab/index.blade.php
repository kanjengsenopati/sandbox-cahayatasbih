<div class="row g-7">
    <!-- Card Download Template -->
    <div class="col-md-5">
        <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px; background: #ffffff;">
            <div class="card-body p-6">
                <div class="mb-5">
                    <h4 class="text-slate-800 fw-semibold fs-5" style="color: #1e293b;">Unduh Template Siswa</h4>
                    <p class="text-slate-400 fst-italic fs-7" style="color: #94a3b8;">Gunakan form ini untuk mengunduh template Excel berisi data siswa berdasarkan UPT dan Kelas pilihan Anda.</p>
                </div>

                <form action="{{ route('bill.download-template') }}" method="GET" id="form-download-template">
                    <!-- Pilih UPT (School) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Unit Pendidikan / UPT <span class="text-danger">*</span></label>
                        <select name="school_id" id="import-school-id" class="form-select form-select-solid" required style="border-radius: 12px;">
                            <option value="">Pilih Unit Pendidikan</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Kelas (Classroom) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Kelas <span class="text-danger">*</span></label>
                        <select name="classroom_id" id="import-classroom-id" class="form-select form-select-solid" required style="border-radius: 12px;" disabled>
                            <option value="">Pilih UPT Terlebih Dahulu</option>
                        </select>
                    </div>

                    <!-- Tombol Download -->
                    <button type="submit" class="btn w-100 text-white fw-bold py-3 mt-2" style="background-color: #2563EB; border-radius: 16px; border: none;">
                        <i class="fas fa-download me-2 text-white"></i> Unduh Template Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Card Upload & Preview -->
    <div class="col-md-7">
        <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px; background: #ffffff;">
            <div class="card-body p-6">
                <div class="mb-5">
                    <h4 class="text-slate-800 fw-semibold fs-5" style="color: #1e293b;">Unggah & Preview Pembayaran</h4>
                    <p class="text-slate-400 fst-italic fs-7" style="color: #94a3b8;">Unggah file Excel yang sudah diisi nominal bayar untuk melakukan validasi data sebelum dikonfirmasi.</p>
                </div>

                <form id="form-preview-import" enctype="multipart/form-data">
                    @csrf
                    <!-- Pilih Nama Tagihan (BillType type=OTHER) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Nama Tagihan Pendaftaran <span class="text-danger">*</span></label>
                        <select name="bill_type_id" id="import-bill-type-id" class="form-select form-select-solid" required style="border-radius: 12px;">
                            <option value="">Pilih Jenis Tagihan Bebas/Pendaftaran</option>
                            @php
                                $billTypes = \App\Models\BillType::where('type', \App\Models\BillType::TYPE_OTHER)
                                    ->with('academicYear')
                                    ->get()
                                    ->unique(function ($item) {
                                        return strtolower($item->name) . '-' . $item->academic_year_id . '-' . strtolower($item->school_type ?? '');
                                    })
                                    ->sortBy('formatted_name');
                            @endphp
                            @foreach ($billTypes as $bt)
                                <option value="{{ $bt->id }}">{{ $bt->formatted_name }} {{ $bt->academicYear ? '('.$bt->academicYear->name.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input File Excel -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">File Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="import-file" class="form-control form-control-solid" accept=".xlsx, .xls" required style="border-radius: 12px;">
                    </div>

                    <!-- Tombol Preview -->
                    <button type="submit" id="btn-preview-import" class="btn text-white fw-bold py-3 px-6" style="background-color: #2563EB; border-radius: 16px; border: none;">
                        <i class="fas fa-eye me-2 text-white"></i> Preview Data Excel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview UI Section (Hidden by Default) -->
<div class="row mt-7 d-none" id="section-preview-import">
    <div class="col-12">
        <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px; background: #ffffff;">
            <div class="card-header border-0 pt-6 px-6 bg-transparent d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="text-slate-800 fw-semibold fs-5 mb-1" style="color: #1e293b;" id="preview-title-tagihan">Preview Data Pembayaran: -</h3>
                    <p class="text-slate-400 fst-italic fs-7" style="color: #94a3b8;">Periksa kembali data pembayaran siswa sebelum menekan tombol konfirmasi. Hanya baris berstatus VALID yang akan diproses.</p>
                </div>
            </div>
            
            <div class="card-body p-6 pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-hover" id="table-preview-data">
                        <thead>
                            <tr class="text-start text-slate-400 fw-bold fs-9 text-uppercase tracking-wider border-bottom border-gray-200" style="color: #94a3b8;">
                                <th style="width: 5%">No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-end">Nominal Bayar</th>
                                <th class="text-center" style="width: 15%">Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-600 fw-semibold" style="color: #475569;">
                            <!-- Data render via JS -->
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-6">
                    <button type="button" class="btn btn-secondary px-6 py-3" id="btn-cancel-import" style="border-radius: 16px;">Batal</button>
                    <button type="button" class="btn text-white px-8 py-3" id="btn-confirm-import" style="background-color: #10B981; border-radius: 16px; border: none;">
                        <i class="fas fa-check me-2 text-white"></i> Konfirmasi & Simpan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        // Load classroom dinamis berdasarkan UPT terpilih
        $('#import-school-id').change(function() {
            var schoolId = $(this).val();
            var classroomSelect = $('#import-classroom-id');
            
            if (schoolId) {
                classroomSelect.prop('disabled', true).html('<option value="">Sedang memuat...</option>');
                
                $.ajax({
                    url: "{{ route('select2') }}",
                    dataType: 'json',
                    data: {
                        data_type: "CLASSROOM_BY_SCHOOL",
                        school_id: schoolId
                    },
                    success: function(data) {
                        classroomSelect.prop('disabled', false).html('<option value="">Semua Kelas</option>');
                        $.each(data, function(index, item) {
                            classroomSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    },
                    error: function() {
                        classroomSelect.prop('disabled', false).html('<option value="">Gagal memuat kelas</option>');
                    }
                });
            } else {
                classroomSelect.prop('disabled', true).html('<option value="">Pilih UPT Terlebih Dahulu</option>');
            }
        });

        var lastImportedData = [];
        var activeBillTypeId = null;

        // Form Submit Preview Excel
        $('#form-preview-import').submit(function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            activeBillTypeId = $('#import-bill-type-id').val();
            
            Swal.fire({
                title: 'Membaca Excel...',
                text: 'Harap tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('bill.preview-import') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success && response.data.length > 0) {
                        lastImportedData = response.data;
                        $('#preview-title-tagihan').text('Preview Data Pembayaran: ' + response.bill_type_name);
                        
                        var tbody = $('#table-preview-data tbody');
                        tbody.empty();
                        
                        var validCount = 0;

                        $.each(response.data, function(index, row) {
                            var no = index + 1;
                            var amountFormatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(row.amount);
                            
                            var badgeClass = row.status === 'VALID' ? 'badge-light-success text-emerald-600' : 'badge-light-danger text-red-600';
                            var badgeText = row.status === 'VALID' ? 'VALID' : 'INVALID';
                            var styleColor = row.status === 'VALID' ? 'color: #10B981 !important;' : 'color: #DC2626 !important;';
                            
                            if (row.status === 'VALID') validCount++;

                            var htmlRow = '<tr class="border-bottom border-gray-100">' +
                                '<td>' + no + '</td>' +
                                '<td class="fw-bold text-slate-800" style="color: #1e293b;">' + row.name + '</td>' +
                                '<td>' + row.classroom + '</td>' +
                                '<td class="text-end fw-bold" style="color: #475569;">' + amountFormatted + '</td>' +
                                '<td class="text-center"><span class="badge ' + badgeClass + ' fw-boldest" style="' + styleColor + '">' + badgeText + '</span></td>' +
                                '<td class="fs-7 text-muted">' + (row.message || '-') + '</td>' +
                                '</tr>';
                            tbody.append(htmlRow);
                        });
                        
                        $('#section-preview-import').removeClass('d-none');
                        
                        // Scroll ke section preview
                        $('html, body').animate({
                            scrollTop: $("#section-preview-import").offset().top - 100
                        }, 500);

                        // Disable tombol confirm jika tidak ada data valid sama sekali
                        if (validCount === 0) {
                            $('#btn-confirm-import').prop('disabled', true).addClass('opacity-50');
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian',
                                text: 'Tidak ada data valid yang bisa diimport. Silakan periksa kembali file Excel Anda.'
                            });
                        } else {
                            $('#btn-confirm-import').prop('disabled', false).removeClass('opacity-50');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Tidak ada baris data siswa yang ditemukan di file Excel.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    var msg = 'Terjadi kesalahan saat membaca file Excel.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                }
            });
        });

        // Batal
        $('#btn-cancel-import').click(function() {
            $('#section-preview-import').addClass('d-none');
            $('#form-preview-import')[0].reset();
            lastImportedData = [];
            activeBillTypeId = null;
        });

        // Konfirmasi & Simpan Pembayaran
        $('#btn-confirm-import').click(function() {
            if (lastImportedData.length === 0 || !activeBillTypeId) return;

            // Hanya kirim data yang VALID
            var validData = lastImportedData.filter(function(item) {
                return item.status === 'VALID';
            });

            if (validData.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Tidak ada data berstatus VALID yang dapat diimport.'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Import',
                text: "Apakah Anda yakin ingin mengimport " + validData.length + " data pembayaran tagihan? Tindakan ini akan langsung mencatat pembayaran berstatus LUNAS.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Import Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan Pembayaran...',
                        text: 'Mohon tunggu',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('bill.confirm-import') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            bill_type_id: activeBillTypeId,
                            data: validData
                        },
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                }).then(() => {
                                    // Reset form & reload page/datatable
                                    $('#btn-cancel-import').click();
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            var msg = 'Gagal menyimpan data import.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
