<div class="row g-7">
    <!-- Card Download Template -->
    <div class="col-md-5">
        <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px; background: #ffffff;">
            <div class="card-body p-6">
                <div class="mb-5">
                    <h4 class="text-slate-800 fw-semibold fs-5" style="color: #1e293b;">Unduh Template Siswa</h4>
                    <p class="text-slate-400 fst-italic fs-7" style="color: #94a3b8;">Gunakan form ini untuk mengunduh template Excel berisi data siswa berdasarkan UPT, Kelas, Tahun Ajaran, dan Jenis Tagihan pilihan Anda.</p>
                </div>

                <form action="{{ route('bill.download-template') }}" method="GET" id="form-download-template">
                    <!-- Pilih UPT (School) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Unit Pendidikan / UPT <span class="text-danger">*</span></label>
                        <select name="school_id" id="import-school-id" class="form-select form-select-solid" required style="border-radius: 12px;">
                            <option value="">Pilih Unit Pendidikan</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" data-school-type="{{ $school->type }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Multi Kelas (Classrooms) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Kelas <span class="text-danger">*</span></label>
                        <select name="classroom_ids[]" id="import-classroom-ids" class="form-select form-select-solid" data-control="select2" data-placeholder="Semua Kelas" multiple="multiple" style="border-radius: 12px;" disabled>
                        </select>
                    </div>

                    <!-- Pilih Tahun Ajaran -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="template-academic-year-id" class="form-select form-select-solid" required style="border-radius: 12px;">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Jenis Tagihan Pembayaran -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Jenis Tagihan Pembayaran</label>
                        <select name="bill_type_ids[]" id="template-bill-type-ids" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Tahun Ajaran Terlebih Dahulu" multiple="multiple" style="border-radius: 12px;" disabled>
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
                    <!-- Pilih UPT (School) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Unit Pendidikan / UPT <span class="text-danger">*</span></label>
                        <select name="school_id" id="import-school-id-upload" class="form-select form-select-solid" required style="border-radius: 12px;">
                            <option value="">Pilih Unit Pendidikan</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" data-school-type="{{ $school->type }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Multi Kelas (Classrooms) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Kelas <span class="text-danger">*</span></label>
                        <select name="classroom_ids[]" id="import-classroom-ids-upload" class="form-select form-select-solid" data-control="select2" data-placeholder="Semua Kelas" multiple="multiple" style="border-radius: 12px;" disabled>
                        </select>
                    </div>

                    <!-- Pilih Tahun Ajaran -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="import-academic-year-id" class="form-select form-select-solid" required style="border-radius: 12px;">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Nama Tagihan Pembayaran (BillType type=OTHER) -->
                    <div class="mb-4">
                        <label class="text-slate-400 fw-bold fs-9 text-uppercase tracking-wider mb-2 d-block" style="color: #94a3b8; font-size: 11px;">Nama Tagihan Pembayaran <span class="text-danger">*</span></label>
                        <select name="bill_type_id" id="import-bill-type-id" class="form-select form-select-solid" required style="border-radius: 12px;" disabled>
                            <option value="">Pilih Tahun Ajaran Terlebih Dahulu</option>
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

<!-- Riwayat Import Pembayaran Section -->
<div class="row mt-7" id="section-import-history">
    <div class="col-12">
        <div class="card shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-0" style="border-radius: 24px; background: #ffffff;">
            <div class="card-header border-0 pt-6 px-6 bg-transparent d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-slate-800 fw-semibold fs-5 mb-1" style="color: #1e293b;">Riwayat Import Pembayaran</h4>
                    <p class="text-slate-400 fst-italic fs-7 mb-0" style="color: #94a3b8;">Daftar seluruh import pembayaran yang pernah dilakukan. Klik tombol <strong>Batal</strong> untuk membatalkan dan mengembalikan status tagihan.</p>
                </div>
            </div>
            <div class="card-body p-6 pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-hover" id="table-import-history" style="width: 100%;">
                        <thead>
                            <tr class="text-start text-slate-400 fw-bold fs-9 text-uppercase tracking-wider border-bottom border-gray-200" style="color: #94a3b8;">
                                <th style="width: 4%;">No</th>
                                <th>UPT Lembaga</th>
                                <th>Tahun Ajaran</th>
                                <th>Jenis Tagihan</th>
                                <th class="text-center">Siswa</th>
                                <th class="text-end">Total Nominal</th>
                                <th>Nama Petugas</th>
                                <th>Timestamp Log</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-600 fw-semibold" style="color: #475569;">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<style>
    /* Custom Dropdown Grid for Classes */
    .custom-kelas-dropdown .select2-results > .select2-results__options {
        display: flex;
        flex-wrap: wrap;
        padding: 0;
    }
    .custom-kelas-dropdown .select2-results__option[role="group"] {
        flex: 1 1 0;
        padding: 0;
        border-right: 1px solid #e2e8f0;
        min-width: 0; 
    }
    .custom-kelas-dropdown .select2-results__option[role="group"]:last-child {
        border-right: none;
    }
    .custom-kelas-dropdown .select2-results__group {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        padding: 8px 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .custom-kelas-dropdown.layout-3-col .select2-results__option[role="group"] {
        min-width: 30%;
    }
    .custom-kelas-dropdown.layout-2-col .select2-results__option[role="group"] {
        min-width: 45%;
    }
    .custom-kelas-dropdown .select2-results__options--nested {
        padding: 4px 0;
    }
    .custom-kelas-dropdown .select2-results__option {
        padding: 6px 12px;
    }
</style>
<script>
    $(document).ready(function() {
        // Fungsi helper untuk merender opsi kelas dengan grouping
        function renderClassroomOptions(schoolName, data, selectElement) {
            selectElement.empty().val(null);
            var isPondok = schoolName.toUpperCase().indexOf('PONDOK') !== -1;
            var isMA = schoolName.toUpperCase().indexOf('MA') !== -1 || schoolName.toUpperCase().indexOf('SMA') !== -1;
            var isSMP = schoolName.toUpperCase().indexOf('SMP') !== -1 || schoolName.toUpperCase().indexOf('MTS') !== -1;

            if (isPondok) {
                // Khusus pondok 2 kolom
                var half = Math.ceil(data.length / 2);
                var g1 = data.slice(0, half);
                var g2 = data.slice(half);
                
                if (g1.length > 0) {
                    var opt1 = $('<optgroup>').attr('label', 'Kelompok 1');
                    $.each(g1, function(i, item) { opt1.append(new Option(item.name, item.id, false, false)); });
                    selectElement.append(opt1);
                }
                if (g2.length > 0) {
                    var opt2 = $('<optgroup>').attr('label', 'Kelompok 2');
                    $.each(g2, function(i, item) { opt2.append(new Option(item.name, item.id, false, false)); });
                    selectElement.append(opt2);
                }
                selectElement.attr('data-col-layout', '2');
            } else {
                // 3 kolom untuk SMP/MA
                var groups = {};
                var others = [];
                $.each(data, function(i, item) {
                    var name = item.name.toUpperCase();
                    var grade = null;
                    if (name.startsWith('7')) grade = 'Kelas 7';
                    else if (name.startsWith('8')) grade = 'Kelas 8';
                    else if (name.startsWith('9')) grade = 'Kelas 9';
                    else if (name.startsWith('10') || name.startsWith('X-') || name === 'X' || name.startsWith('X ')) grade = 'Kelas 10';
                    else if (name.startsWith('11') || name.startsWith('XI-') || name === 'XI' || name.startsWith('XI ')) grade = 'Kelas 11';
                    else if (name.startsWith('12') || name.startsWith('XII-') || name === 'XII' || name.startsWith('XII ')) grade = 'Kelas 12';
                    else grade = 'Lainnya';
                    
                    if (!groups[grade]) groups[grade] = [];
                    groups[grade].push(item);
                });

                var order = isSMP ? ['Kelas 7', 'Kelas 8', 'Kelas 9', 'Lainnya'] : ['Kelas 10', 'Kelas 11', 'Kelas 12', 'Lainnya'];
                
                $.each(order, function(i, key) {
                    if (groups[key] && groups[key].length > 0) {
                        var opt = $('<optgroup>').attr('label', key);
                        $.each(groups[key], function(j, item) { opt.append(new Option(item.name, item.id, false, false)); });
                        selectElement.append(opt);
                    }
                });
                
                $.each(groups, function(key, items) {
                    if (order.indexOf(key) === -1 && items.length > 0) {
                        var opt = $('<optgroup>').attr('label', key);
                        $.each(items, function(j, item) { opt.append(new Option(item.name, item.id, false, false)); });
                        selectElement.append(opt);
                    }
                });
                selectElement.attr('data-col-layout', '3');
            }
            selectElement.trigger('change');
        }

        // Terapkan custom class pada Select2 dropdown saat terbuka
        $('#import-classroom-ids, #import-classroom-ids-upload').on('select2:open', function (e) {
            var selectId = $(this).attr('id');
            var dropdown = $('#select2-' + selectId + '-results').closest('.select2-dropdown');
            var layout = $(this).attr('data-col-layout') || '3';
            
            dropdown.addClass('custom-kelas-dropdown');
            dropdown.removeClass('layout-2-col layout-3-col').addClass('layout-' + layout + '-col');
        });

        // Load classroom dinamis (Multi-Select) berdasarkan UPT terpilih
        $('#import-school-id').change(function() {
            var schoolId = $(this).val();
            var schoolName = $(this).find('option:selected').text();
            var classroomSelect = $('#import-classroom-ids');
            
            classroomSelect.empty().val(null).trigger('change');
            if (schoolId) {
                classroomSelect.prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('select2') }}",
                    dataType: 'json',
                    data: {
                        data_type: "CLASSROOM_BY_SCHOOL",
                        school_id: schoolId
                    },
                    success: function(data) {
                        classroomSelect.prop('disabled', false);
                        renderClassroomOptions(schoolName, data, classroomSelect);
                    },
                    error: function() {
                        classroomSelect.prop('disabled', false);
                    }
                });
            } else {
                classroomSelect.prop('disabled', true).trigger('change');
            }
        });

        // Load classroom dinamis (Multi-Select) berdasarkan UPT terpilih (Upload Form)
        $('#import-school-id-upload').change(function() {
            var schoolId = $(this).val();
            var schoolName = $(this).find('option:selected').text();
            var classroomSelect = $('#import-classroom-ids-upload');
            
            classroomSelect.empty().val(null).trigger('change');
            if (schoolId) {
                classroomSelect.prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('select2') }}",
                    dataType: 'json',
                    data: {
                        data_type: "CLASSROOM_BY_SCHOOL",
                        school_id: schoolId
                    },
                    success: function(data) {
                        classroomSelect.prop('disabled', false);
                        renderClassroomOptions(schoolName, data, classroomSelect);
                    },
                    error: function() {
                        classroomSelect.prop('disabled', false);
                    }
                });
            } else {
                classroomSelect.prop('disabled', true).trigger('change');
            }
        });

        // Master data bill types — DATABASE-DRIVEN via BillType.bill_item_id → BillItem
        @php
            // Mapping School.type → BillItem names (same as ReportTransactionController)
            $schoolTypeToBillItemMap = [
                \App\Models\School::TYPE_SMP    => ['SMP'],
                \App\Models\School::TYPE_MA      => ['MADRASAH ALIYAH'],
                \App\Models\School::TYPE_PONDOK  => ['PONDOK'],
            ];

            // Preload BillItem IDs grouped by School.type
            $billItemIdsBySchoolType = [];
            foreach ($schoolTypeToBillItemMap as $schoolType => $billItemNames) {
                $ids = \App\Models\BillItem::whereIn('name', $billItemNames)->pluck('id')->toArray();
                $billItemIdsBySchoolType[$schoolType] = $ids;
            }

            // Build flat lookup: bill_item_id → school_type
            $billItemToSchoolType = [];
            foreach ($billItemIdsBySchoolType as $schoolType => $ids) {
                foreach ($ids as $id) {
                    $billItemToSchoolType[$id] = $schoolType;
                }
            }

            $allTemplateBillTypes = \App\Models\BillType::with(['academicYear', 'billItem'])->get()->sortBy(function($bt) {
                return ($bt->billItem->name ?? '') . ' - ' . $bt->name;
            })->values()->map(function($bt) use ($billItemToSchoolType) {
                $resolvedSchoolType = $billItemToSchoolType[$bt->bill_item_id] ?? null;
                $suffix = $bt->billItem->name ?? '';
                $displayName = $bt->name;
                // Append BillItem name as suffix if not already in the name
                if ($suffix && !str_contains(strtolower($displayName), strtolower($suffix))) {
                    $displayName .= ' - ' . $suffix;
                }
                return [
                    'id' => $bt->id,
                    'name' => $displayName . ($bt->academicYear ? ' ('.$bt->academicYear->name.')' : ''),
                    'academic_year_id' => $bt->academic_year_id,
                    'bill_item_id' => $bt->bill_item_id,
                    'school_type' => $resolvedSchoolType,
                ];
            });

            $allUploadBillTypes = $allTemplateBillTypes;
        @endphp

        var allTemplateBillTypes = @json($allTemplateBillTypes);
        var allUploadBillTypes = @json($allUploadBillTypes);

        // Database-driven mapping: School.type → BillItem IDs
        var billItemIdsBySchoolType = @json($billItemIdsBySchoolType);

        // Helper: mendapatkan school_type dari UPT yang dipilih
        function getSelectedSchoolType(selectId) {
            var $select = $(selectId);
            var $selected = $select.find('option:selected');
            return $selected.data('school-type') || null;
        }

        // Helper: mendapatkan BillItem IDs yang valid untuk school_type tertentu
        function getAllowedBillItemIds(schoolType) {
            if (!schoolType) return null; // null = tidak filter
            return billItemIdsBySchoolType[String(schoolType).toUpperCase()] || [];
        }

        // Fungsi rebuild bill type dropdown (dipakai oleh kedua event: UPT change & Tahun Ajaran change)
        function rebuildTemplateBillTypes() {
            var academicYearId = $('#template-academic-year-id').val();
            var schoolType = getSelectedSchoolType('#import-school-id');
            var allowedBillItemIds = getAllowedBillItemIds(schoolType);
            var billTypeSelect = $('#template-bill-type-ids');

            // Destroy Select2, wipe options, rebuild
            if (billTypeSelect.hasClass('select2-hidden-accessible')) {
                billTypeSelect.select2('destroy');
            }
            billTypeSelect.empty();

            if (academicYearId) {
                var filtered = allTemplateBillTypes.filter(function(bt) {
                    var matchYear = String(bt.academic_year_id) === String(academicYearId);
                    // Database-driven: filter by bill_item_id
                    var matchSchool = !allowedBillItemIds || allowedBillItemIds.indexOf(bt.bill_item_id) !== -1;
                    return matchYear && matchSchool;
                });

                if (filtered.length > 0) {
                    $.each(filtered, function(i, bt) {
                        billTypeSelect.append(new Option(bt.name, bt.id, false, false));
                    });
                    billTypeSelect.prop('disabled', false);
                } else {
                    billTypeSelect.prop('disabled', true);
                }
                billTypeSelect.select2({ placeholder: 'Pilih Jenis Tagihan', allowClear: true });
            } else {
                billTypeSelect.prop('disabled', true);
                billTypeSelect.select2({ placeholder: 'Pilih Tahun Ajaran Terlebih Dahulu' });
            }
        }

        function rebuildUploadBillTypes() {
            var academicYearId = $('#import-academic-year-id').val();
            var schoolType = getSelectedSchoolType('#import-school-id-upload');
            var allowedBillItemIds = getAllowedBillItemIds(schoolType);
            var billTypeSelect = $('#import-bill-type-id');

            // Wipe and rebuild native select
            billTypeSelect.empty();

            if (academicYearId) {
                var filtered = allUploadBillTypes.filter(function(bt) {
                    var matchYear = String(bt.academic_year_id) === String(academicYearId);
                    // Database-driven: filter by bill_item_id
                    var matchSchool = !allowedBillItemIds || allowedBillItemIds.indexOf(bt.bill_item_id) !== -1;
                    return matchYear && matchSchool;
                });

                if (filtered.length > 0) {
                    billTypeSelect.append('<option value="">Pilih Jenis Tagihan Pembayaran</option>');
                    $.each(filtered, function(i, bt) {
                        billTypeSelect.append(new Option(bt.name, bt.id, false, false));
                    });
                    billTypeSelect.prop('disabled', false);
                } else {
                    billTypeSelect.append('<option value="">Tidak ada tagihan untuk tahun ajaran ini</option>');
                    billTypeSelect.prop('disabled', true);
                }
            } else {
                billTypeSelect.append('<option value="">Pilih Tahun Ajaran Terlebih Dahulu</option>');
                billTypeSelect.prop('disabled', true);
            }
        }

        // Strict cascade filter: Jenis Tagihan Pembayaran (Download Template)
        // Trigger rebuild on BOTH UPT change AND Tahun Ajaran change
        $('#template-academic-year-id').change(function() {
            rebuildTemplateBillTypes();
        });

        // Saat UPT berubah di form download, juga rebuild bill types
        $('#import-school-id').change(function() {
            rebuildTemplateBillTypes();
        });

        // Strict cascade filter: Nama Tagihan Pembayaran (Upload & Preview)
        $('#import-academic-year-id').change(function() {
            rebuildUploadBillTypes();
        });

        // Saat UPT berubah di form upload, juga rebuild bill types
        $('#import-school-id-upload').change(function() {
            rebuildUploadBillTypes();
        });

        var lastImportedData = [];
        var activeBillTypeId = null;
        var activeAcademicYearId = null;

        // Form Submit Preview Excel
        $('#form-preview-import').submit(function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            activeBillTypeId = $('#import-bill-type-id').val();
            activeAcademicYearId = $('#import-academic-year-id').val();
            
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
                        var titleText = 'Preview Data Pembayaran: ' + response.bill_type_name;
                        if (response.academic_year_name) {
                            titleText += ' (' + response.academic_year_name + ')';
                        }
                        $('#preview-title-tagihan').text(titleText);
                        
                        var tbody = $('#table-preview-data tbody');
                        tbody.empty();
                        
                        var validCount = 0;

                        $.each(response.data, function(index, row) {
                            var no = index + 1;
                            var amountFormatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(row.amount);
                            
                            var badgeClass = 'badge-light-danger text-red-600';
                            var badgeText = row.status;
                            var styleColor = 'color: #DC2626 !important;';

                            if (row.status === 'VALID') {
                                badgeClass = 'badge-light-success text-emerald-600';
                                badgeText = 'VALID';
                                styleColor = 'color: #10B981 !important;';
                                validCount++;
                            } else if (row.status === 'SKIPPED') {
                                badgeClass = 'badge-light-secondary text-slate-500';
                                badgeText = 'DIABAIKAN';
                                styleColor = 'color: #64748b !important;';
                            } else {
                                badgeText = 'INVALID';
                            }

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
            $('#import-academic-year-id').trigger('change');
            lastImportedData = [];
            activeBillTypeId = null;
            activeAcademicYearId = null;
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
                            academic_year_id: activeAcademicYearId,
                            bill_type_id: activeBillTypeId,
                            school_id: $('#import-school-id-upload').val(),
                            classroom_info: $('#import-classroom-ids-upload option:selected').map(function() { return $(this).text(); }).get().join(', '),
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

        // ========================================
        // RIWAYAT IMPORT - DataTable
        // ========================================
        var importHistoryTable = $('#table-import-history').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('bill.import-logs') }}",
                type: 'GET',
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'school_name', name: 'school_name' },
                { data: 'academic_year_name', name: 'academic_year_name' },
                { data: 'bill_type_name', name: 'bill_type_name' },
                { data: 'total_students', name: 'total_students', className: 'text-center' },
                { data: 'total_amount_formatted', name: 'total_amount_formatted', className: 'text-end' },
                { data: 'admin_name', name: 'admin_name' },
                { data: 'timestamp', name: 'timestamp' },
                { data: 'status_badge', name: 'status_badge', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            order: [[7, 'desc']],
            language: {
                emptyTable: "Belum ada riwayat import.",
                processing: "Memuat data...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                paginate: { previous: "‹", next: "›" }
            },
            drawCallback: function() {
                // Style pagination
                $('#table-import-history_wrapper .dataTables_paginate .paginate_button').css({
                    'border-radius': '8px',
                    'margin': '0 2px'
                });
            }
        });

        // ========================================
        // ROLLBACK IMPORT - Event Handler
        // ========================================
        $(document).on('click', '.btn-rollback-import', function() {
            var importLogId = $(this).data('id');

            Swal.fire({
                title: 'Batalkan Import?',
                html: '<p class="mb-2">Tindakan ini akan:</p>' +
                      '<ul class="text-start" style="font-size: 14px;">' +
                      '<li>Mengembalikan status tagihan ke <strong>UNPAID</strong></li>' +
                      '<li>Menghapus seluruh transaksi terkait import ini</li>' +
                      '</ul>' +
                      '<p class="mt-2 text-danger fw-bold">Apakah Anda yakin?</p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fas fa-undo me-1"></i> Ya, Batalkan Import',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Rollback...',
                        text: 'Mohon tunggu, data sedang dikembalikan.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('bill/rollback-import') }}/" + importLogId,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Dibatalkan',
                                    text: response.message,
                                }).then(() => {
                                    importHistoryTable.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            var msg = 'Gagal melakukan rollback.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg,
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
