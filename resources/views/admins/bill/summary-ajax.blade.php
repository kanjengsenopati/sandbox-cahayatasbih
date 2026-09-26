



    
    
        
            <div class="d-flex flex-column flex-lg-row">
                <!-- Content -->
                <div class="flex-lg-row-fluid  order-2 order-lg-1 mb-10 mb-lg-0">
                    <div class="card border-0">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0 text-dark">Ringkasan Tagihan</h2>

                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Informasi Siswa -->
                                <div class="col-md-6">
                                    <h3 class="h4 mb-3">Informasi Siswa</h3>
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Nama</th>
                                                <td>{{ $student->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Saldo</th>
                                                <td>Rp. {{ number_format($student->saldo ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Kelas</th>
                                                <td>{{ $student->classroom->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">NISN</th>
                                                <td>{{ $student->nisn ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Sekolah</th>
                                                <td>{{ $student->classroom?->school->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Wali Murid</th>
                                                <td>
                                                    {{ $student->user?->name ?? '-' }}
                                                    @if($student->user?->jamaah_status)
                                                        @php
                                                            $status = $student->user->jamaah_status;
                                                            $badgeClass = match($status) {
                                                                'JAMAAH' => 'success',
                                                                'NON_JAMAAH' => 'danger',
                                                                'MUKIMIN' => 'primary',
                                                                default => 'danger'
                                                            };
                                                            $statusLabel = match($status) {
                                                                'JAMAAH' => 'Jamaah',
                                                                'NON_JAMAAH' => 'Non Jamaah',
                                                                'MUKIMIN' => 'Mukimin',
                                                                default => 'Non Jamaah'
                                                            };
                                                        @endphp
                                                        <span class="badge badge-light-{{ $badgeClass }} fw-bolder ms-2 px-2 py-1">
                                                            {{ $statusLabel }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Informasi Tagihan -->
                                <div class="col-md-6">
                                    <h3 class="h4 mb-3">Informasi Tagihan</h3>
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Nama</th>
                                                <td>{{ $billType->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Tahun Ajaran</th>
                                                <td>{{ $billType->academicYear->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total Tagihan</th>
                                                <td>Rp. {{ number_format($summary['total_bill'] ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total Dibayar</th>
                                                <td>Rp. {{ number_format($summary['total_paid'] ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Sisa Tagihan</th>
                                                <td>Rp. {{ number_format($summary['total_unpaid'] ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Status</th>
                                                <td>
                                                    @if(($summary['total_bill'] ?? 0) > 0)
                                                        @if(($summary['total_unpaid'] ?? 0) == 0)
                                                            <span class="badge badge-light-success fw-bolder px-2 py-1">LUNAS</span>
                                                        @else
                                                            <span class="badge badge-light-danger fw-bolder px-2 py-1">BELUM LUNAS</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-light-secondary fw-bolder px-2 py-1">TIDAK ADA TAGIHAN</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <a href="{{ route('bill.index', ['student_id' => $student->id]) }}"
                                    class="btn btn-primary" style="background-color: #4D0C7A; border-color: #4D0C7A;">
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bill Payment -->
                    <div class="card card-flush pt-3 mb-5 mb-lg-10">
                        <div class="card-header">
                            <div class="card-title">
                                <h2 class="fw-bolder">Pembayaran Tagihan</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 fw-bold gy-4"
                                    id="kt_subscription_products_table">
                                    <thead>
                                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%;">No</th>
                                            <th class="min-w-125px">Periode</th>
                                            <th class="min-w-125px">Tagihan</th>
                                            <th class="min-w-125px">Metode Pembayaran</th>
                                            <th>Status</th>
                                            <th class="min-w-125px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600">
                                        @foreach (array_merge(range(7, 12), range(1, 6)) as $month)
                                        @php
                                        $billForMonth = $bills->firstWhere('month', (int)$month) ?? $bills->firstWhere('month', (string)$month);
                                        
                                        $isZarkasi = str_contains(strtoupper($billType->name ?? ''), 'ZARKASI');
                                        $isAplikasi = str_contains(strtoupper($billType->name ?? ''), 'APLIKASI');
                                        $isSyahriah = str_contains(strtoupper($billType->name ?? ''), 'SYAHR');

                                        if ($isZarkasi) {
                                            $targetZarkasi = [7=>100000, 8=>100000, 9=>100000, 10=>100000, 11=>100000, 12=>50000];
                                            $rawAmount = $targetZarkasi[$month] ?? 0;
                                        } elseif ($isAplikasi) {
                                            $rawAmount = 10000;
                                        } elseif ($isSyahriah) {
                                            $rawAmount = 500000;
                                        } else {
                                            $rawAmount = $billForMonth ? $billForMonth->amount : ($billType->billItem->amount ?? 0);
                                            if ($rawAmount <= 0) {
                                                $rawAmount = \App\Models\Bill::where('bill_type_id', $billType->id)->where('amount', '>', 0)->value('amount') ?? 0;
                                            }
                                        }

                                        $displayAmount = $billForMonth ? $billForMonth->amount : $rawAmount;
                                        if ($displayAmount <= 0 && $rawAmount > 0) {
                                            $displayAmount = $rawAmount;
                                        }

                                        $targetYear = $billForMonth?->year ?? ($month >= 7 ? 
                                            ($billType->academicYear?->start_year ?? date('Y')) : 
                                            ($billType->academicYear?->end_year ?? (date('Y') + 1)));

                                        $amountFormatted = number_format($displayAmount, 0, ',', '.');
                                        $transaction = $billForMonth?->transactions?->first();
                                        $adminName = $transaction?->admin?->name ?? '';
                                        $paidAt = $transaction?->paid_at;
                                        $paymentMethodType = $transaction?->paymentMethod?->type;
                                        $paymentMethodName = $transaction?->paymentMethod?->name ?? '';
                                        $status = $billForMonth ? $billForMonth->translated_status : 'UNPAID';
                                        $isPaid = $billForMonth && $billForMonth->status == 'PAID';
                                        $isUnpaid = !$isPaid;
                                        $paymentLink = $billForMonth?->transactions?->first()?->payment_link;
                                        $autoBillId = $billForMonth ? $billForMonth->id : "auto_{$billType->id}_{$month}_{$targetYear}";
                                        @endphp

                                        <tr>
                                            <form action="{{ route('bill.store') }}" method="post" class="form-bayar"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <td>{{ $loop->iteration }}</td>
                                                <th class="min-w-125px">
                                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                                    - {{ $targetYear }}
                                                </th>

                                                <input type="hidden" name="bill_ids[]"
                                                    value="{{ $autoBillId }}">
                                                <input type="hidden" name="pay_amount"
                                                    value="{{ $displayAmount }}">
                                                <input type="hidden" name="student_id" value="{{ $student->id ?? '' }}">
                                                <td>Rp {{ $amountFormatted }}</td>
                                                <td>
                                                    @if ($isPaid)
                                                    <span class="badge badge-success">
                                                        @if ($paymentMethodType == 'CASH')
                                                        Tunai Melalui {{ $adminName }}
                                                        @elseif ($paymentMethodType == 'TRANSFER')
                                                        Transfer Diverifikasi oleh {{ $adminName }}
                                                        @elseif ($paymentMethodType == 'BALANCE')
                                                        @if ($transaction?->admin_id)
                                                        Debit Saldo melalui {{ $adminName }}
                                                        @else
                                                        Debit Saldo
                                                        @endif
                                                        @else
                                                        {{ $paymentMethodName }}
                                                        @endif
                                                        @if ($paidAt)
                                                        <br>Dibayar {{ $paidAt }}
                                                        @endif
                                                    </span>
                                                    @else
                                                    <select class="form-select payment-method-select"
                                                        name="payment_method" required>
                                                        <option value="">Metode Pembayaran</option>
                                                        @if ($student->saldo >= $displayAmount)
                                                        <option value="BALANCE">Saldo</option>
                                                        @endif
                                                        <option value="CASH">Tunai</option>
                                                    </select>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($isPaid)
                                                        <span class="badge badge-success px-3 py-2 fs-8 fw-bolder">LUNAS</span>
                                                    @else
                                                        <span class="badge badge-danger px-3 py-2 fs-8 fw-bolder">Belum Lunas</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $user = Auth::user();
                                                        $canEditStatus = false;
                                                        if ($user) {
                                                            if ($user->hasRole('Super Admin') || $user->can('Edit Status Tagihan')) {
                                                                $canEditStatus = true;
                                                            } elseif ($user->hasRole('Bendahara')) {
                                                                $username = strtolower($user->username ?? '');
                                                                $name = strtolower($user->name ?? '');
                                                                if (
                                                                    str_contains($username, 'khoirus') || 
                                                                    str_contains($username, 'paramita') ||
                                                                    str_contains($name, 'khoirus') || 
                                                                    str_contains($name, 'paramita')
                                                                ) {
                                                                    $canEditStatus = true;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if ($isPaid)
                                                            @if ($canEditStatus && $billForMonth)
                                                                <a onclick="changeStatus('{{ $billForMonth->id }}', 'UNPAID')"
                                                                    class="btn btn-danger btn-sm text-nowrap">
                                                                    <i class="fas fa-undo me-1"></i> Batalkan
                                                                </a>
                                                            @else
                                                                <span class="badge badge-light-success fs-9 fw-bold">Terbayar</span>
                                                            @endif
                                                        @else
                                                            @if ($canEditStatus && $billForMonth)
                                                                <a onclick="changeStatus('{{ $billForMonth->id }}', 'PAID')"
                                                                    class="btn btn-success btn-sm text-nowrap">
                                                                    <i class="fas fa-check me-1"></i> Ubah Status
                                                                </a>
                                                            @endif
                                                            @if ($isUnpaid && $paymentLink)
                                                                <a href="{{ $paymentLink }}" class="btn btn-primary btn-sm text-nowrap">Ke Halaman Pembayaran</a>
                                                            @else
                                                                <button type="button" class="btn btn-primary btn-bayar btn-sm text-nowrap">Bayar</button>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                            </form>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const selectAllCb = document.getElementById('select-all-summary');
        const btnPay = document.getElementById('btn-pay-selected');
        const countSpan = document.getElementById('selected-count');

        function updateBtn() {
            const checked = document.querySelectorAll('.bill-month-checkbox:checked');
            if(countSpan) countSpan.innerText = checked.length;
            if(btnPay) btnPay.style.display = checked.length > 0 ? 'inline-block' : 'none';
        }

        if (selectAllCb) {
            selectAllCb.addEventListener('change', e => {
                document.querySelectorAll('.bill-month-checkbox').forEach(cb => {
                    if(!cb.disabled) cb.checked = e.target.checked;
                });
                updateBtn();
            });
        }

        $(document).off('change', '.bill-month-checkbox').on('change', '.bill-month-checkbox', updateBtn);

        // Trigger modal population
        if(btnPay) {
            btnPay.addEventListener('click', function() {
                $('#modal-rincian').modal('hide');
                $('#paymentModal').modal('show');
                const checked = document.querySelectorAll('.bill-month-checkbox:checked');
                const detailsContainer = document.getElementById('payment-details');
                const totalEl = document.getElementById('total-amount');
                const form = document.getElementById('form-multi-payment');
                
                if(detailsContainer) detailsContainer.innerHTML = '';
                document.querySelectorAll('input[name="bill_ids[]"]').forEach(el => el.remove());

                let total = 0;
                checked.forEach(cb => {
                    const billId = cb.getAttribute('data-bill-id');
                    const amt = parseInt(cb.getAttribute('data-amount'));
                    const monthName = cb.getAttribute('data-month');
                    const year = cb.getAttribute('data-year');
                    const billName = cb.getAttribute('data-bill-name');

                    total += amt;

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'bill_ids[]';
                    hidden.value = billId;
                    form.appendChild(hidden);

                    const col = document.createElement('div');
                    col.className = 'col-md-6 mb-2';
                    col.innerHTML = `
                        <div class="card h-100 border border-gray-200 shadow-none" style="border-radius: 12px; background-color: #f8fafc;">
                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-column text-start">
                                    <span class="fw-bold fs-7 text-slate-800">${billName}</span>
                                    <span class="text-slate-500 fs-8 mt-1">${monthName} ${year}</span>
                                </div>
                                <div class="text-end">
                                    <span class="fw-boldest fs-7 text-slate-900">Rp ${amt.toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    detailsContainer.appendChild(col);
                });

                if(totalEl) totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');

                const studentBalance = parseInt('{{ $student->saldo ?? 0 }}');
                const paymentMethod = document.getElementById('payment-method');
                if (paymentMethod) {
                    const balanceOption = paymentMethod.querySelector('option[value="BALANCE"]');
                    if (balanceOption) {
                        if (studentBalance < total) {
                            balanceOption.style.display = 'none';
                            balanceOption.disabled = true;
                            if (paymentMethod.value === 'BALANCE') paymentMethod.value = '';
                        } else {
                            balanceOption.style.display = 'block';
                            balanceOption.disabled = false;
                        }
                    }
                }
            });
        }
        
        $('#form-multi-payment').on('submit', function() {
            const btn = $('#btn-submit-payment');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Loading...');
        });
    });
</script>






   function changeStatus(billId, status) {
        var title = status === 'PAID' ? 'Ubah Status Tagihan' : 'Batalkan Pembayaran';
        var text = status === 'PAID' 
            ? 'Apakah Anda yakin ingin mengubah status tagihan ini menjadi LUNAS?' 
            : 'Apakah Anda yakin ingin MEMBATALKAN pembayaran tagihan ini? Transaksi pembayaran akan dihapus/di-rollback.';
        var confirmText = status === 'PAID' ? 'Ya, Ubah!' : 'Ya, Batalkan!';
        var confirmColor = status === 'PAID' ? '#10B981' : '#DC2626';
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.action = "{{ route('bill.change-status') }}";
                form.method = 'post';
                form.enctype = 'multipart/form-data';

                form.innerHTML = `
                @csrf
                <input type="hidden" name="status" value="${status}">
                <input type="hidden" name="bill_id" value="${billId}">
                `;

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
