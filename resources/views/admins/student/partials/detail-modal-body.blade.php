<div class="modal-header border-0 pb-0 pt-6 px-7 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <div class="symbol symbol-50px symbol-circle me-2">
            <img src="{{ $student->avatar_url ?? asset('assets/media/avatars/default.png') }}"
                 onerror="this.src='{{ asset('assets/media/avatars/default.png') }}'" alt="Avatar Student">
        </div>
        <div>
            <h3 class="modal-title fw-bolder text-gray-900 fs-4 mb-0">{{ $student->name }}</h3>
            <span class="text-slate-400 text-[12px]">NIS: {{ $student->nis ?? '-' }} &bull; NISN: {{ $student->nisn ?? '-' }}</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        @can('Edit Santri')
        <button type="button" class="btn btn-sm btn-primary rounded-[24px] btn-open-edit-modal me-1" data-id="{{ $student->id }}">
            <i class="fa fa-edit me-1"></i> Edit Siswa
        </button>
        @endcan
        <button type="button" class="btn btn-icon btn-sm btn-active-light-primary rounded-circle" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa fa-times fs-5 text-gray-500"></i>
        </button>
    </div>
</div>

<div class="modal-body pt-5 px-7">
    <div class="mb-5 hover-scroll-x">
        <ul class="nav nav-tabs flex-nowrap text-nowrap border-bottom-0 gap-2" id="modalStudentTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-active-light-primary rounded-[24px] active"
                   data-bs-toggle="tab" href="#modal_tab_info" role="tab">
                    <i class="fa fa-user me-1"></i> Informasi Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-active-light-primary rounded-[24px]"
                   data-bs-toggle="tab" href="#modal_tab_bill" role="tab">
                    <i class="fa fa-file-invoice-dollar me-1"></i> Tagihan Santri
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-active-light-primary rounded-[24px]"
                   data-bs-toggle="tab" href="#modal_tab_saldo" role="tab">
                    <i class="fa fa-wallet me-1"></i> Riwayat Saldo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-active-light-primary rounded-[24px]"
                   data-bs-toggle="tab" href="#modal_tab_saving" role="tab">
                    <i class="fa fa-piggy-bank me-1"></i> Riwayat Tabungan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-active-light-primary rounded-[24px]"
                   data-bs-toggle="tab" href="#modal_tab_tahfidz" role="tab">
                    <i class="fa fa-quran me-1"></i> Tahfidz
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="modalStudentTabContent">
        <!-- Tab 1: Informasi Siswa -->
        <div class="tab-pane fade show active" id="modal_tab_info" role="tabpanel">
            <div class="row g-6">
                <!-- Column 1 -->
                <div class="col-md-6">
                    <div class="card card-bordered rounded-[24px] p-5 shadow-none bg-light-subtle h-100">
                        <h4 class="fw-bold text-gray-800 fs-6 mb-4 border-bottom pb-2">Data Diri & Identitas</h4>
                        <table class="table table-borderless table-sm mb-0 text-[14px]">
                            <tr>
                                <td class="text-slate-400 fw-medium" style="width: 40%">Nama Lengkap</td>
                                <td style="width: 5%">:</td>
                                <td class="fw-semibold text-gray-800">
                                    {{ $student->name }}
                                    <span class="badge {{ $student->isAlumniSmpMa() ? 'badge-light-success' : 'badge-light-secondary text-gray-700' }} ms-2 px-2 py-1 fs-9">
                                        {{ $student->isAlumniSmpMa() ? 'Alumni' : 'Non Alumni' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Nama Panggilan</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->nickname ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Tempat, Tgl Lahir</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">
                                    {{ $student->born_place ?? '-' }},
                                    @if(!empty($student->birth_date))
                                        {{ \Carbon\Carbon::parse($student->birth_date)->format('d-M-Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Jenis Kelamin</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">NIS</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->nis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">NISN</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->nisn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Status</td>
                                <td>:</td>
                                <td>
                                    @php
                                        $statusClass = match($student->status) {
                                            'ACTIVE' => 'badge-light-success',
                                            'INACTIVE' => 'badge-light-danger',
                                            'GRADUATED' => 'badge-light-primary',
                                            default => 'badge-light-warning'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} fw-bold px-2 py-1">{{ $student->status }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Saldo Utama</td>
                                <td>:</td>
                                <td class="fw-bold text-emerald-600">Rp {{ number_format($student->saldo, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Saldo Tabungan</td>
                                <td>:</td>
                                <td class="fw-bold text-emerald-600">Rp {{ number_format($student->saving, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="col-md-6">
                    <div class="card card-bordered rounded-[24px] p-5 shadow-none bg-light-subtle h-100">
                        <h4 class="fw-bold text-gray-800 fs-6 mb-4 border-bottom pb-2">Wali, Sekolah & Asrama</h4>
                        <table class="table table-borderless table-sm mb-0 text-[14px]">
                            <tr>
                                <td class="text-slate-400 fw-medium" style="width: 45%">Wali Siswa</td>
                                <td style="width: 5%">:</td>
                                <td class="fw-semibold text-gray-800">
                                    {{ $student->user->name ?? 'Belum diatur' }}
                                    @if($student->user && $student->user->jamaah_status)
                                        @php
                                            $st = $student->user->jamaah_status;
                                            $bClass = match($st) {
                                                'JAMAAH' => 'badge-light-success',
                                                'NON_JAMAAH' => 'badge-light-danger',
                                                'MUKIMIN' => 'badge-light-primary',
                                                default => 'badge-light-danger'
                                            };
                                        @endphp
                                        <span class="badge {{ $bClass }} fw-bold ms-1 px-2 py-0.5 text-[11px]">
                                            {{ $st }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">UPT / Sekolah</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->classroom?->school?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Kelas</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->classroom?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Penanggung Jawab / Ustadz Kamar</td>
                                <td>:</td>
                                <td>
                                    <span class="badge badge-light-secondary text-slate-700 fw-bold border border-slate-200">
                                        {{ $student->asramaHost->name ?? ($student->asrama?->hostAdmin?->name ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Nama Kamar</td>
                                <td>:</td>
                                <td>
                                    <span class="badge badge-light-secondary text-slate-700 fw-bold border border-slate-200">
                                        {{ $student->asrama_name ?? ($student->asrama?->name ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Alamat</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Kota / Kabupaten</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->city ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-slate-400 fw-medium">Provinsi</td>
                                <td>:</td>
                                <td class="fw-semibold text-gray-800">{{ $student->province ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Tagihan Santri -->
        <div class="tab-pane fade" id="modal_tab_bill" role="tabpanel">
            <div class="table-responsive">
                <table id="modal-table-student-bill" class="table align-middle table-row-dashed fs-7 gy-3 w-100">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th style="width: 5%">No</th>
                            <th>Tahun Ajaran</th>
                            <th>Kelas</th>
                            <th>Item Pembayaran</th>
                            <th>Total Tagihan</th>
                            <th>Dibayar</th>
                            <th>Sisa Tagihan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold"></tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Riwayat Saldo -->
        <div class="tab-pane fade" id="modal_tab_saldo" role="tabpanel">
            <div class="table-responsive">
                <table id="modal-table-saldo-history" class="table align-middle table-row-dashed fs-7 gy-3 w-100">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th style="width: 5%">No</th>
                            <th>Siswa</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold"></tbody>
                </table>
            </div>
        </div>

        <!-- Tab 4: Riwayat Tabungan -->
        <div class="tab-pane fade" id="modal_tab_saving" role="tabpanel">
            <div class="table-responsive">
                <table id="modal-table-saving-history" class="table align-middle table-row-dashed fs-7 gy-3 w-100">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th style="width: 5%">No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold"></tbody>
                </table>
            </div>
        </div>

        <!-- Tab 5: Tahfidz -->
        <div class="tab-pane fade" id="modal_tab_tahfidz" role="tabpanel">
            <div class="table-responsive">
                <table id="modal-table-tahfidz" class="table align-middle table-row-dashed fs-7 gy-3 w-100">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th style="width: 5%">No</th>
                            <th>Tanggal</th>
                            <th>Santri</th>
                            <th>Jumlah Halaman</th>
                            <th>Keterangan</th>
                            <th>Feedback</th>
                            <th>Link Video</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var studentId = "{{ $student->id }}";
        var showUrl = "{{ route('student.show', $student->id) }}";
        var modalTables = {};

        $.fn.dataTable.ext.errMode = 'none';

        function initBillTableModal() {
            if (modalTables['bill']) return;
            modalTables['bill'] = $('#modal-table-student-bill').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: showUrl,
                    type: 'GET',
                    data: { type: 'bill' }
                },
                columns: [
                    { data: null, render: function(d, t, r, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                    { data: 'academic_year.name', name: 'academic_year.name' },
                    { data: 'classroom', name: 'classroom' },
                    { data: 'name', name: 'name' },
                    { data: 'total', name: 'total' },
                    { data: 'total_paid', name: 'total_paid' },
                    { data: 'total_unpaid', name: 'total_unpaid' },
                    { data: 'status', name: 'status', className: 'text-center' }
                ]
            });
        }

        function initSaldoTableModal() {
            if (modalTables['saldo']) return;
            modalTables['saldo'] = $('#modal-table-saldo-history').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: showUrl,
                    type: 'GET',
                    data: { type: 'saldo' }
                },
                columns: [
                    { data: null, render: function(d, t, r, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                    { data: 'student.name', name: 'student.name' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status', name: 'status' },
                    { data: 'description', name: 'description' }
                ]
            });
        }

        function initSavingTableModal() {
            if (modalTables['saving']) return;
            modalTables['saving'] = $('#modal-table-saving-history').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: showUrl,
                    type: 'GET',
                    data: { type: 'saving' }
                },
                columns: [
                    { data: null, render: function(d, t, r, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                    { data: 'date', name: 'date' },
                    { data: 'student.name', name: 'student.name' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status', name: 'status' },
                    { data: 'description', name: 'description' }
                ]
            });
        }

        function initTahfidzTableModal() {
            if (modalTables['tahfidz']) return;
            modalTables['tahfidz'] = $('#modal-table-tahfidz').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: showUrl,
                    type: 'GET',
                    data: { type: 'tahfidz' }
                },
                columns: [
                    { data: null, render: function(d, t, r, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                    { data: 'deposit_date', name: 'deposit_date' },
                    { data: 'student.name', name: 'student.name' },
                    { data: 'number_of_pages', name: 'number_of_pages' },
                    { data: 'note', name: 'note' },
                    { data: 'feedback', name: 'feedback' },
                    { data: 'link', name: 'link' }
                ]
            });
        }

        $('#modalStudentTabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href");
            if (target === "#modal_tab_bill") {
                initBillTableModal();
                if (modalTables['bill']) modalTables['bill'].columns.adjust().responsive.recalc();
            } else if (target === "#modal_tab_saldo") {
                initSaldoTableModal();
                if (modalTables['saldo']) modalTables['saldo'].columns.adjust().responsive.recalc();
            } else if (target === "#modal_tab_saving") {
                initSavingTableModal();
                if (modalTables['saving']) modalTables['saving'].columns.adjust().responsive.recalc();
            } else if (target === "#modal_tab_tahfidz") {
                initTahfidzTableModal();
                if (modalTables['tahfidz']) modalTables['tahfidz'].columns.adjust().responsive.recalc();
            }
        });
    })();
</script>
