@extends('layouts.master', ['title' => 'Penyesuaian Saldo Siswa'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar mb-5" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Penyesuaian Saldo Siswa</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('saldo-history.index') }}" class="text-muted text-hover-primary">Riwayat Saldo</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Penyesuaian Saldo</li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('saldo-history.index') }}" class="btn btn-sm btn-light-primary fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card border-0" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04);">
                <!--begin::Card Header-->
                <div class="card-header border-0 pt-6 pb-4">
                    <div class="card-title d-flex align-items-center gap-3 flex-wrap">
                        <div class="d-flex align-items-center position-relative me-2">
                            <span class="svg-icon svg-icon-1 position-absolute ms-4">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                            <input type="text" id="custom-search" class="form-control form-control-solid w-250px ps-12 fs-7" placeholder="Cari santri (min. 3 huruf)..." />
                        </div>

                        <div class="w-200px">
                            <select id="filter-classroom" class="form-select form-select-solid fs-7">
                                <option value="">Semua Kelas</option>
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="card-toolbar d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-danger btn-sm rounded-[24px] fw-bold d-none shadow-sm" id="btn-batch-reset-zero" onclick="executeBatchResetZero()">
                            <i class="fas fa-undo me-1"></i> Reset Saldo Ke Rp 0 <span class="badge badge-circle badge-white ms-1 text-danger" id="selected-zero-count">0</span>
                        </button>
                        <span class="badge badge-light-primary fs-7 px-4 py-3 rounded-pill">
                            <i class="fas fa-info-circle text-primary me-1"></i> Update Saldo Langsung pada Tabel
                        </span>
                    </div>
                </div>
                <!--end::Card Header-->

                <!--begin::Card Body-->
                <div class="card-body pt-0 px-6 pb-6">
                    <x-alert.alert-validation />
                    
                    <div class="table-responsive">
                        <table id="table-adjust-saldo" class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                                <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-200">
                                    <th style="width: 3%" class="text-center pe-0">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" id="check-all-students" />
                                        </div>
                                    </th>
                                    <th style="width: 4%">No</th>
                                    <th style="width: 12%">NIS</th>
                                    <th style="width: 22%">Nama</th>
                                    <th style="width: 10%">Kelas</th>
                                    <th style="width: 14%">Saldo Awal</th>
                                    <th style="width: 14%">Saldo Sekarang</th>
                                    <th class="text-center" style="width: 21%">Aksi Update Inline</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-bold"></tbody>
                        </table>
                    </div>
                </div>
                <!--end::Card Body-->
            </div>
            <!--end::Card-->
        </div>
    </div>
    <!--end::Post-->
</div>
@endsection

@push('js')
<script>
    var table = null;

    $(document).ready(function() {
        table = $('#table-adjust-saldo').DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            order: [],
            ajax: {
                url: "{{ route('saldo-history.create') }}",
                data: function(d) {
                    d.classroom_id = $('#filter-classroom').val();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Memuat data...",
                "processing": "Sedang memproses...",
                "search": "",
                "searchPlaceholder": "Cari santri (min. 3 huruf)..."
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    sortable: false,
                    searchable: false,
                    className: 'text-center pe-0',
                    render: function(data, type, row) {
                        return `<div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input student-select-checkbox" type="checkbox" value="${data}" data-saldo="${row.saldo || 0}" data-name="${row.name}" />
                                </div>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'nis',
                    name: 'nis',
                    orderable: true,
                    sortable: true,
                    searchable: true,
                    render: function(data) {
                        return `<span class="fw-bold text-gray-700 fs-7">${data ? data : '-'}</span>`;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: true,
                    sortable: true,
                    searchable: true,
                    render: function(data, type, row) {
                        var avatar = row.avatar_url ? row.avatar_url : '{{ asset("assets/media/avatars/default.png") }}';
                        var statusText = row.translated_status || row.status || 'Aktif';
                        var badgeClass = 'bg-light-success text-success';

                        if (row.status === 'INACTIVE') {
                            badgeClass = 'bg-light-danger text-danger';
                        } else if (row.status === 'GRADUATED') {
                            badgeClass = 'bg-light-warning text-warning';
                        } else if (row.status === 'TRANSFERRED') {
                            badgeClass = 'bg-light-info text-info';
                        } else if (row.status === 'DROPPED_OUT') {
                            badgeClass = 'bg-light-secondary text-secondary';
                        }

                        return `
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-35px me-3">
                                    <img src="${avatar}" alt="${data}" style="object-fit: cover;" />
                                </div>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="text-gray-800 text-hover-primary fw-bolder fs-6 mb-1">${data}</span>
                                    <span class="badge ${badgeClass} fs-8 px-2 py-1">${statusText}</span>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'classroom',
                    name: 'classroom',
                    orderable: true,
                    sortable: true,
                    searchable: true,
                    render: function(data) {
                        return `<span class="badge badge-light-dark fs-7">${data}</span>`;
                    }
                },
                {
                    data: 'saldo',
                    name: 'saldo',
                    orderable: true,
                    sortable: true,
                    searchable: false,
                    render: function(data, type, row) {
                        var val = parseInt(data) || 0;
                        var formatted = formatRupiahVal(val);
                        var badgeClass = val < 0 
                            ? 'bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1' 
                            : 'bg-success text-white fw-bolder px-3 py-2 fs-7 mb-1';
                        var dateInfo = row.last_saldo_update_date || '-';
                        var timeInfo = row.last_saldo_update_time || '-';

                        return `
                            <div class="d-flex flex-column align-items-start">
                                <span class="badge ${badgeClass}" id="saldo-awal-${row.id}" data-saldo="${val}">Rp ${formatted}</span>
                                <span class="text-slate-400 fst-italic mt-1" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="date-awal-${row.id}">${dateInfo}</span>
                                <span class="text-slate-400 fst-italic" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="time-awal-${row.id}">${timeInfo}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'saldo',
                    name: 'saldo_sekarang',
                    orderable: true,
                    sortable: true,
                    searchable: false,
                    render: function(data, type, row) {
                        var val = parseInt(data) || 0;
                        var formatted = formatRupiahVal(val);
                        var badgeClass = val < 0 
                            ? 'bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1' 
                            : 'bg-success text-white fw-bolder px-3 py-2 fs-7 mb-1';
                        var dateInfo = row.last_saldo_update_date || '-';
                        var timeInfo = row.last_saldo_update_time || '-';

                        return `
                            <div class="d-flex flex-column align-items-start">
                                <span class="badge ${badgeClass}" id="saldo-sekarang-${row.id}">Rp ${formatted}</span>
                                <span class="text-slate-400 fst-italic mt-1" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="date-sekarang-${row.id}">${dateInfo}</span>
                                <span class="text-slate-400 fst-italic" style="font-size: 11px; line-height: 1.3; color: #94a3b8;" id="time-sekarang-${row.id}">${timeInfo}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    sortable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <select class="form-select form-select-sm form-select-solid type-select fs-7" id="type-${row.id}" style="width: 105px;" onchange="recalculateSaldo('${row.id}')">
                                    <option value="IN">+ TopUp</option>
                                    <option value="WITHDRAW">- Tarik</option>
                                </select>
                                <div class="input-group input-group-sm" style="width: 140px;">
                                    <span class="input-group-text bg-light text-gray-600 border-0 fs-7 px-2">Rp</span>
                                    <input type="text" class="form-control form-control-sm form-control-solid amount-input fs-7 px-2" id="amount-${row.id}" placeholder="0" onkeyup="onAmountKeyUp(this, '${row.id}')">
                                </div>
                                <input type="text" class="form-control form-control-sm form-control-solid desc-input fs-7" id="desc-${row.id}" placeholder="Keterangan..." style="width: 130px;">
                                <button type="button" class="btn btn-sm btn-primary px-3 py-2 fs-7 btn-save-inline" id="btn-save-${row.id}" onclick="submitInlineSaldo('${row.id}')">
                                    <i class="fas fa-check me-1"></i> Update
                                </button>
                            </div>
                        `;
                    }
                }
            ]
        });

        // Filter classroom change event
        $('#filter-classroom').change(function() {
            table.ajax.reload();
        });

        // Custom Search Input: only filter if search query is at least 3 characters or empty
        var searchTimer;
        $('#custom-search').on('keyup input', function() {
            clearTimeout(searchTimer);
            var val = $(this).val().trim();
            
            searchTimer = setTimeout(function() {
                if (val.length >= 3) {
                    table.search(val).draw();
                } else if (val.length === 0) {
                    table.search('').draw();
                }
            }, 300);
        });
    });

    // Helper to format number to Rupiah string
    function formatRupiahVal(number) {
        var val = parseInt(number) || 0;
        return val.toLocaleString('id-ID');
    }

    // Parse input string into raw integer amount
    function parseAmountStr(str) {
        if (!str) return 0;
        var clean = str.toString().replace(/[^0-9]/g, '');
        return parseInt(clean) || 0;
    }

    // On keyup handler for inline amount input
    function onAmountKeyUp(inputElem, rowId) {
        var rawVal = parseAmountStr($(inputElem).val());
        if (rawVal > 0) {
            $(inputElem).val(rawVal.toLocaleString('id-ID'));
        } else {
            $(inputElem).val('');
        }
        recalculateSaldo(rowId);
    }

    // Recalculate "Saldo Sekarang" live preview
    function recalculateSaldo(rowId) {
        var baseSaldo = parseInt($('#saldo-awal-' + rowId).attr('data-saldo')) || 0;
        var type = $('#type-' + rowId).val();
        var amount = parseAmountStr($('#amount-' + rowId).val());
        
        var newSaldo = baseSaldo;
        if (type === 'IN') {
            newSaldo = baseSaldo + amount;
        } else if (type === 'WITHDRAW') {
            newSaldo = baseSaldo - amount;
        }

        var badgeElem = $('#saldo-sekarang-' + rowId);
        badgeElem.text('Rp ' + formatRupiahVal(newSaldo));

        if (newSaldo < 0) {
            badgeElem.attr('class', 'badge bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1');
        } else {
            badgeElem.attr('class', 'badge bg-light-success text-success fw-bolder fs-7 mb-1');
        }
    }

    // Save inline balance adjustment via AJAX
    function submitInlineSaldo(rowId) {
        var amountStr = $('#amount-' + rowId).val();
        var amount = parseAmountStr(amountStr);
        var type = $('#type-' + rowId).val();
        var desc = $('#desc-' + rowId).val();
        var btn = $('#btn-save-' + rowId);

        if (amount <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Masukkan jumlah nominal penyesuaian yang valid terlebih dahulu.',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' }
            });
            return;
        }

        // Disable button & show spinner
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>...');

        axios.post("{{ route('saldo-history.store') }}", {
            student_id: rowId,
            type: type,
            amount: amount,
            description: desc,
            _token: "{{ csrf_token() }}"
        }, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Update');

            var resData = response.data;
            if (resData && (resData.code == 200 || resData.code == '200' || resData.new_saldo !== undefined)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: resData.message || 'Saldo santri berhasil diperbarui.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Update Saldo Awal attribute, text, and badge class
                var newSaldo = resData.new_saldo !== undefined ? resData.new_saldo : 0;
                var awalBadge = $('#saldo-awal-' + rowId);
                awalBadge.attr('data-saldo', newSaldo).text('Rp ' + formatRupiahVal(newSaldo));
                if (newSaldo < 0) {
                    awalBadge.attr('class', 'badge bg-danger text-white fw-bolder px-3 py-2 fs-7 mb-1');
                } else {
                    awalBadge.attr('class', 'badge bg-light-primary text-primary fw-bolder fs-7 mb-1');
                }

                // Update date & time under Saldo Awal and Saldo Sekarang
                if (resData.updated_date && resData.updated_time) {
                    $('#date-awal-' + rowId).text(resData.updated_date);
                    $('#time-awal-' + rowId).text(resData.updated_time);
                    $('#date-sekarang-' + rowId).text(resData.updated_date);
                    $('#time-sekarang-' + rowId).text(resData.updated_time);
                }
                
                // Clear input fields and recalculate
                $('#amount-' + rowId).val('');
                $('#desc-' + rowId).val('');
                recalculateSaldo(rowId);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: (resData && resData.message) ? resData.message : 'Terjadi kesalahan saat menyimpan penyesuaian saldo.'
                });
            }
        })
        .catch(function(error) {
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Update');
            
            var msg = 'Terjadi kesalahan pada server saat memperbarui saldo.';
            if (error.response && error.response.data && error.response.data.message) {
                msg = error.response.data.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Gagal Update Saldo',
                text: msg
            });
        });
    }

    // Multi-select Checkbox Handler
    $(document).on('change', '#check-all-students', function() {
        var isChecked = $(this).is(':checked');
        $('.student-select-checkbox').prop('checked', isChecked);
        updateBatchButtonState();
    });

    $(document).on('change', '.student-select-checkbox', function() {
        var allCount = $('.student-select-checkbox').length;
        var checkedCount = $('.student-select-checkbox:checked').length;
        $('#check-all-students').prop('checked', allCount > 0 && allCount === checkedCount);
        updateBatchButtonState();
    });

    function updateBatchButtonState() {
        var checkedCount = $('.student-select-checkbox:checked').length;
        var btn = $('#btn-batch-reset-zero');
        var counter = $('#selected-zero-count');

        if (checkedCount > 0) {
            btn.removeClass('d-none');
            counter.text(checkedCount);
        } else {
            btn.addClass('d-none');
            counter.text('0');
        }
    }

    // Execute Batch Reset Saldo to Rp 0 via Async AJAX
    function executeBatchResetZero() {
        var selectedBoxes = $('.student-select-checkbox:checked');
        var selectedIds = [];

        selectedBoxes.each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih minimal satu santri yang ingin di-reset saldonya ke Rp 0.',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' }
            });
            return;
        }

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            html: 'Sistem akan me-reset saldo dari <b>' + selectedIds.length + ' santri terpilih</b> menjadi <b>Rp 0</b>. Aksi ini akan mencatat riwayat penyesuaian otomatis.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset Ke Rp 0!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger rounded-[24px]',
                cancelButton: 'btn btn-light rounded-[24px]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Reset Saldo...',
                    text: 'Mohon tunggu sebentar, penyesuaian saldo sedang dilakukan.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                axios.post("{{ route('saldo-history.batch-reset-zero') }}", {
                    student_ids: selectedIds,
                    _token: "{{ csrf_token() }}"
                }, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    var resData = response.data;
                    if (resData && (resData.code == 200 || resData.code == '200')) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Reset!',
                            text: resData.message || 'Saldo santri terpilih berhasil di-reset menjadi Rp 0.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Uncheck select-all and hide button
                        $('#check-all-students').prop('checked', false);
                        updateBatchButtonState();

                        // Auto-refresh DataTables live update asynchronously
                        if (table) {
                            table.ajax.reload(null, false);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: (resData && resData.message) ? resData.message : 'Terjadi kesalahan saat me-reset saldo.'
                        });
                    }
                })
                .catch(function(error) {
                    var msg = 'Terjadi kesalahan pada server saat me-reset saldo.';
                    if (error.response && error.response.data && error.response.data.message) {
                        msg = error.response.data.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Reset Saldo',
                        text: msg
                    });
                });
            }
        });
    }
</script>
@endpush