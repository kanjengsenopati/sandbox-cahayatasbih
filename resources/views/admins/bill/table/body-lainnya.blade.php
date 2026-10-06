@push('css')
<style>
    .payment-card {
        background-color: #f5f5f5;
        border: none;
        border-bottom: 1px solid #dcdcdc;
        margin-bottom: 0;
        padding: 2px;
    }
    #payment-details .payment-card:last-child {
        border-bottom: none;
    }
    .month-card {
        transition: all 0.2s ease;
        border: 1px solid #e4e6ef;
    }
    .month-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .month-card.paid {
        background-color: #f0fdf4;
        border-color: #10b981;
    }
    .month-card.unpaid {
        background-color: #fffbeb;
        border-color: #f59e0b;
    }
    .month-card.selected {
        background-color: #e0f2fe !important; /* Sky-100 (Primary accent tint) */
        border-color: #2563eb !important;     /* Accent Primary */
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08), 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
    }
    .month-card.clickable-payment-card,
    .month-card.clickable-payment-card * {
        cursor: pointer !important;
        user-select: none;
    }
    .form-check-custom .form-check-input {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
@endpush

@php
    $canManageBill = auth()->user()->hasRole('Super Admin') || auth()->user()->can('Create Tagihan');
    $canPayBill = auth()->user()->hasRole('Super Admin') || auth()->user()->can('Create Tagihan') || auth()->user()->can('Edit Tagihan');
@endphp

<div class="accordion" id="accordionLainnyaParent">
    @if($canManageBill && isset($ungeneratedOtherRates) && $ungeneratedOtherRates->isNotEmpty())
    {{-- Ada tarif yang sudah di-mapping admin tapi belum di-generate → tampilkan shortcut buttons --}}
    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 my-4">
        <span class="svg-icon svg-icon-2tx svg-icon-primary me-4">
            <i class="fas fa-bolt fs-1 text-primary"></i>
        </span>
        <div class="d-flex flex-column flex-grow-1">
            <div class="fw-bold">
                <h4 class="text-gray-900 fw-bolder mb-1">Tagihan Siap Diterbitkan</h4>
                <div class="fs-6 text-gray-700 mb-3">
                    Tagihan berikut sudah dikonfigurasi untuk kelas siswa <strong>{{ $student->name }}</strong> tetapi belum diterbitkan. Klik tombol di bawah untuk langsung menerbitkan tagihan:
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @foreach($ungeneratedOtherRates as $ur)
                <div class="d-flex align-items-center bg-white rounded-3 px-4 py-3" style="box-shadow: 0 2px 8px rgba(0,0,0,0.04); min-width: 260px;">
                    <div class="flex-grow-1 me-3">
                        <div class="fw-bold text-gray-800 fs-6">{{ $ur->bill_type_name }}</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge badge-light-primary fs-8">{{ $ur->academic_year_name }}</span>
                            @if($ur->amount > 0)
                            <span class="fw-semibold text-emerald-600 fs-7" style="color: #10B981;">Rp {{ number_format($ur->amount, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                    <button type="button"
                        class="btn btn-sm btn-primary generate-lainnya-btn hover-scale"
                        data-rate-id="{{ $ur->rate_id }}"
                        data-student-id="{{ $student->id }}"
                        data-bill-type-name="{{ $ur->bill_type_name }}"
                        title="Terbitkan tagihan {{ $ur->bill_type_name }} untuk {{ $student->name }}">
                        <i class="fas fa-sync-alt me-1"></i>Terbitkan Sekarang
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($billOthers->isEmpty() && (!isset($ungeneratedOtherRates) || $ungeneratedOtherRates->isEmpty()))
    {{-- Tidak ada tagihan DAN tidak ada tarif yang bisa di-generate --}}
    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 my-4">
        <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
            <i class="fas fa-exclamation-triangle fs-1 text-warning"></i>
        </span>
        <div class="d-flex flex-stack flex-grow-1">
            <div class="fw-bold">
                <h4 class="text-gray-900 fw-bolder">Belum Ada Tagihan Lainnya yang Di-generate</h4>
                <div class="fs-6 text-gray-700">
                    Tagihan kategori <strong>Lainnya</strong> (Bebas / Fix maupun Bebas / Cicilan seperti LKS, Pendaftaran, dll) belum dibuat/di-generate untuk siswa <strong>{{ $student->name }}</strong>.
                    <br>
                    <span class="text-muted fs-7 mt-2 d-inline-block">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        <strong>Panduan Admin:</strong> Silakan masuk ke menu <a href="{{ route('bill-type.index') }}" class="fw-bolder text-primary">Data Jenis Bayar</a>, lalu klik tombol sinkronisasi <i class="fas fa-sync text-success me-1"></i> <strong>Generasi Tagihan</strong> pada kelas siswa ini ({{ $displayClassName }}).
                    </span>

                        @if($canManageBill)
                        <form action="{{ route('bill.generate-student-bills', ['student_id' => $student->id, 'academic_year_id' => request('academic_year_id')]) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-sync-alt me-1"></i> Generate Bill Lainnya
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($billOthers->isNotEmpty())
    <div class="row g-4">
        @foreach ($billOthers as $bill)
        @php
            $paidAmount = $bill->bills->where('student_id', $student->id)->sum('paid_amount');
            $unpaidAmount = $bill->total_unpaid;
            $ayName = $bill->academicYear?->name ?? '-';

            // Color theme map for distinct Academic Years
            $ayColorThemes = [
                '2026/2027' => [
                    'bg' => 'linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%)',
                    'shadow' => 'rgba(37, 99, 235, 0.3)',
                ],
                '2025/2026' => [
                    'bg' => 'linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%)',
                    'shadow' => 'rgba(124, 58, 237, 0.3)',
                ],
                '2024/2025' => [
                    'bg' => 'linear-gradient(135deg, #0f766e 0%, #0d9488 100%)',
                    'shadow' => 'rgba(13, 148, 136, 0.3)',
                ],
                '2023/2024' => [
                    'bg' => 'linear-gradient(135deg, #b45309 0%, #d97706 100%)',
                    'shadow' => 'rgba(217, 119, 6, 0.3)',
                ],
            ];

            $fallbackPalettes = [
                ['bg' => 'linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%)', 'shadow' => 'rgba(37, 99, 235, 0.3)'],
                ['bg' => 'linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%)', 'shadow' => 'rgba(124, 58, 237, 0.3)'],
                ['bg' => 'linear-gradient(135deg, #0f766e 0%, #0d9488 100%)', 'shadow' => 'rgba(13, 148, 136, 0.3)'],
                ['bg' => 'linear-gradient(135deg, #b45309 0%, #d97706 100%)', 'shadow' => 'rgba(217, 119, 6, 0.3)'],
                ['bg' => 'linear-gradient(135deg, #be123c 0%, #e11d48 100%)', 'shadow' => 'rgba(225, 29, 72, 0.3)'],
                ['bg' => 'linear-gradient(135deg, #1e293b 0%, #334155 100%)', 'shadow' => 'rgba(51, 65, 85, 0.3)'],
            ];

            $currentAyTheme = $ayColorThemes[$ayName] ?? $fallbackPalettes[abs(crc32($ayName)) % count($fallbackPalettes)];
        @endphp
        <div class="col-12">
            <div class="accordion-item mb-5 border border-gray-300 shadow-sm rounded-3 overflow-hidden">
        <h2 class="accordion-header" id="headingLainnya{{ $bill->id }}">
            <button class="accordion-button fs-4 fw-bold collapsed bg-light text-dark d-block" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseLainnya{{ $bill->id }}" 
                aria-expanded="false" 
                aria-controls="collapseLainnya{{ $bill->id }}">
                
                <div class="row w-100 align-items-center pe-3">
                    <!-- Left: Title & Category -->
                    <div class="col-md-4 col-12 d-flex flex-column justify-content-center text-start">
                         <div class="d-flex align-items-center flex-wrap gap-2">
                             <span class="text-slate-900 fs-5 fw-bolder me-1">{{ $bill->name }}</span>
                             <span class="badge badge-warning fw-bold fs-8 px-3 py-1 text-white">Tagihan Lain</span>
                             @if(($bill->payment_input_type ?? 'FIXED') === 'FREE')
                                 <span class="badge badge-light-warning fw-bold fs-8 px-2 py-1" title="Pengaturan: Nominal Bebas (Cicilan)">Cicilan</span>
                             @else
                                 <span class="badge badge-light-primary fw-bold fs-8 px-2 py-1" title="Pengaturan: Fix Amount (Nominal Tetap)">Fix</span>
                             @endif
                         </div>
                    </div>

                    <!-- Middle: Prominent Strong Solid 1-Line Tahun Ajaran Badge -->
                    <div class="col-md-4 col-12 my-2 my-md-0 d-flex align-items-center justify-content-start justify-content-md-center px-md-4">
                        <div class="d-inline-flex align-items-center shadow-sm text-nowrap" 
                             style="background: {{ $currentAyTheme['bg'] }}; color: #ffffff; border-radius: 20px; box-shadow: 0 3px 10px {{ $currentAyTheme['shadow'] }}; padding: 6px 14px; gap: 8px;">
                            <i class="fas fa-calendar-alt text-white fs-7 opacity-90 me-1"></i>
                            <span class="fs-7 fw-bold text-white">
                                Tahun Ajaran {{ $ayName }}
                            </span>
                        </div>
                    </div>

                    <!-- Right: Stats & Action -->
                    <div class="col-md-4 col-12 d-flex justify-content-start justify-content-md-end align-items-center mt-2 mt-md-0 gap-2 gap-md-3">
                         <!-- Paid Stat -->
                         <div class="d-flex flex-column align-items-start align-items-md-end">
                             <span class="fs-8 text-slate-500 fw-bold text-uppercase mb-1">Terbayar</span>
                             <span class="badge badge-success fs-7 fw-bolder px-3 py-1 text-white">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                         </div>
 
                         <!-- Unpaid Stat -->
                         <div class="d-flex flex-column align-items-start align-items-md-end border-start border-gray-300 ps-3">
                             <span class="fs-8 text-slate-500 fw-bold text-uppercase mb-1">Sisa Tagihan</span>
                             <span class="badge badge-danger fs-7 fw-bolder px-3 py-1 text-white">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</span>
                         </div>
                         
                         <div class="d-none d-md-block ms-1 text-slate-400 fs-8 fw-bold">
                            Lihat Rincian
                         </div>
                    </div>
                </div>
            </button>
        </h2>

        <div id="collapseLainnya{{ $bill->id }}" class="accordion-collapse collapse" aria-labelledby="headingLainnya{{ $bill->id }}">
            <div class="accordion-body bg-white border-top p-4 p-md-5">
                @if(($bill->payment_input_type ?? 'FIXED') === 'FREE')
                    <div class="row g-4 g-lg-5">
                        <!-- Kolom Kiri: Pilihan Pembayaran (Rasio ~30%) -->
                        <div class="col-xl-4 col-lg-4 col-md-5 col-12">
                            <h4 class="fs-6 fw-boldest text-slate-800 mb-3">
                                <i class="fas fa-file-invoice text-primary me-2"></i> Pilihan Pembayaran
                            </h4>
                            <div class="row g-3">
                                @php
                                    $studentBills = $bill->bills->where('student_id', $student->id);
                                    $hasSpecificMonthBills = $studentBills->filter(fn($b) => (int)$b->month >= 1 && (int)$b->month <= 12)->isNotEmpty();
                                    $monthsToLoop = $hasSpecificMonthBills ? array_merge(range(7, 12), range(1, 6)) : $studentBills->pluck('month')->unique()->values()->all();
                                    if (empty($monthsToLoop)) {
                                        $monthsToLoop = [0];
                                    }
                                @endphp
                                @foreach ($monthsToLoop as $month)
                                @php
                                    $billDetail = $studentBills->firstWhere('month', $month) ?? ($month === 0 ? $studentBills->first() : null);
                                    $amount = $billDetail ? $billDetail->amount : 0;
                                    $paidAmountDetail = $billDetail ? $billDetail->paid_amount : 0;
                                    $remainingAmount = $billDetail ? max(0, $amount - $paidAmountDetail) : 0;
                                    $status = $billDetail ? $billDetail->status : 'UNPAID';
                                    // Tagihan LUNAS HANYA JIKA sisa tagihan <= 0 dan total tagihan > 0
                                    $isPaid = $billDetail && ($remainingAmount <= 0 && $amount > 0);
                                    
                                    $modalId = "bayarLainnya{$bill->id}_{$month}";
                                    // Selama belum lunas (sisa > 0), modal/checkbox pembayaran WAJIB muncul
                                    $showModal = $billDetail && !$isPaid && $remainingAmount > 0;
                                    
                                    $cardClass = $isPaid ? 'paid' : ($remainingAmount > 0 ? 'unpaid' : 'bg-secondary bg-opacity-10');
                                @endphp
                                @if($billDetail)
                                <div class="col-12">
                                    <div class="month-card rounded-3 p-3 px-md-4 {{ $cardClass }} {{ ($showModal && $canPayBill) ? 'cursor-pointer clickable-payment-card' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between gap-3">
                                            <!-- Left side: Tagihan & Badge (Bebas / Cicilan, bukan bulanan) -->
                                            <div class="d-flex flex-column align-items-start gap-1">
                                                <span class="fw-bold fs-6 text-slate-800">
                                                    {{ $bill->name }}
                                                </span>
                                                <span class="badge badge-secondary fs-9 text-slate-600 fw-bold">
                                                    {{ $bill->academicYear?->name ?? ($billDetail->year ?? '-') }}
                                                </span>
                                            </div>
            
                                            <!-- Middle side: Nominal -->
                                            <div class="d-flex align-items-center ms-auto me-3">
                                                @if($billDetail->paid_amount > 0 && !$isPaid)
                                                    <div class="d-flex flex-column text-end">
                                                        <span class="fw-bolder fs-5 text-amber-600">
                                                            Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                                                        </span>
                                                        <span class="fs-9 text-slate-400">Sisa dari Rp {{ number_format($billDetail->amount, 0, ',', '.') }}</span>
                                                    </div>
                                                @else
                                                    <div class="d-flex flex-column text-end">
                                                        <span class="fw-bolder fs-5 {{ $isPaid ? 'text-emerald-600' : ($remainingAmount > 0 ? 'text-amber-600' : 'text-slate-400') }}">
                                                            Rp {{ number_format($isPaid ? ($billDetail->paid_amount ?: $billDetail->amount) : $remainingAmount, 0, ',', '.') }}
                                                        </span>
                                                        @if(!$isPaid)
                                                        <span class="fs-9 text-slate-400">Total Tagihan</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
            
                                            <!-- Right side: Status / Checkbox -->
                                            <div>
                                                @if($isPaid)
                                                    <span class="badge badge-success fw-bolder px-3 py-1.5 text-white">
                                                        <i class="fas fa-check-circle me-1 text-white"></i> Lunas
                                                    </span>
                                                @elseif($showModal)
                                                    @if($canPayBill)
                                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                        <input type="checkbox" 
                                                            name="bill_months[{{ $bill->id }}][]" 
                                                            value="{{ $month }}"
                                                            id="bill-other-{{ $bill->id }}-{{ $month }}"
                                                            class="form-check-input bill-month-checkbox bill-{{ $bill->id }} cursor-pointer" 
                                                            data-bill-id="{{ $billDetail->id }}"
                                                            data-month="Cicilan Bebas" 
                                                            data-year="{{ $bill->academicYear?->name ?? ($billDetail->year ?? '') }}"
                                                            data-bill-name="{{ $bill->name }}" 
                                                            data-amount="{{ $remainingAmount }}"
                                                            data-payment-input-type="FREE"
                                                            onclick="event.stopPropagation()">
                                                        <label class="form-check-label fw-bold text-slate-700 ms-2 fs-7 cursor-pointer" for="bill-other-{{ $bill->id }}-{{ $month }}" onclick="event.stopPropagation()">
                                                            Bayar Cicilan
                                                        </label>
                                                    </div>
                                                    @else
                                                    <span class="badge badge-light-warning text-warning fw-bold fs-8">Belum Lunas</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-light text-slate-400 fs-8">-</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan: Riwayat Pembayaran (Rasio ~70%) -->
                        <div class="col-xl-8 col-lg-8 col-md-7 col-12 border-start border-gray-200 ps-md-5">
                            <h4 class="fs-6 fw-boldest text-slate-800 mb-3">
                                <i class="fas fa-history text-primary me-2"></i> Riwayat Pembayaran
                            </h4>
                            @php
                                $allBillIds = $bill->bills->where('student_id', $student->id)->pluck('id')->filter()->toArray();
                                $historyDetails = collect([]);
                                if (!empty($allBillIds)) {
                                    $historyDetails = \App\Models\TransactionDetail::whereIn('bill_id', $allBillIds)
                                        ->whereNull('transaction_details.deleted_at')
                                        ->whereHas('transaction', function($q) {
                                            $q->whereNull('transactions.deleted_at')
                                              ->whereIn('status', [\App\Models\Transaction::STATUS_PAID, 'paid', 'PAID', 'approved', 'APPROVED', 'SUCCESS', 'success', 'LUNAS', 'lunas']);
                                        })
                                        ->with(['transaction.admin', 'transaction.paymentMethod'])
                                        ->orderBy('created_at', 'asc')
                                        ->get();
                                }
                            @endphp
                            
                            @if(count($historyDetails) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle table-rounded border gy-3 gs-4 fs-7 fw-bold">
                                        <thead>
                                            <tr class="fw-boldest text-slate-700 bg-light text-uppercase tracking-wider">
                                                <th style="width: 5%">No</th>
                                                <th>Tgl Transaksi</th>
                                                <th>Nominal Bayar</th>
                                                <th>Petugas</th>
                                                <th>Sisa Tagihan</th>
                                                <th class="text-center" style="width: 12%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalBillForStudent = $bill->bills->where('student_id', $student->id)->sum('amount');
                                                $runningRemaining = $totalBillForStudent;
                                            @endphp
                                            @foreach($historyDetails as $detail)
                                                @php
                                                    $paidAmt = $detail->amount ?? $firstBillDetail->amount;
                                                    $runningRemaining -= $paidAmt;
                                                    $txDate = $detail->transaction->paid_at ? date('d/m/Y H:i', strtotime($detail->transaction->paid_at)) : '-';
                                                @endphp
                                                <tr class="text-slate-600">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $txDate }}</td>
                                                    <td class="text-emerald-600 fw-boldest">Rp {{ number_format($paidAmt, 0, ',', '.') }}</td>
                                                    <td>
                                                        @if($detail->transaction->paymentMethod?->type == \App\Models\PaymentMethod::TYPE_BALANCE || $detail->saldo_history_id)
                                                            <span class="badge badge-light-primary fw-bolder px-2 py-0.5 fs-9">SALDO</span>
                                                        @else
                                                            {{ $detail->transaction->admin->name ?? 'Sistem / Admin' }}
                                                        @endif
                                                    </td>
                                                    <td class="text-danger fw-boldest">Rp {{ number_format(max(0, $runningRemaining), 0, ',', '.') }}</td>
                                                    <td class="text-center">
                                                        @if(Auth::user()?->hasRole('Super Admin') || Auth::user()?->can('Batal Angsuran Tagihan'))
                                                            <button type="button" 
                                                                class="btn btn-sm btn-light-danger fw-bold py-1 px-2.5 fs-8 btn-cancel-transaction"
                                                                title="Batalkan Angsuran Transaksi Ini"
                                                                onclick="handleCancelTransactionDetail('{{ $detail->id }}', 'Rp {{ number_format($paidAmt, 0, ',', '.') }}', '{{ $txDate }}', event)">
                                                                <i class="fas fa-undo me-1 fs-9"></i> Batal
                                                            </button>
                                                        @else
                                                            <span class="text-muted fs-8">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 bg-light rounded-3 text-slate-400 fs-7">
                                    <i class="fas fa-info-circle me-1"></i> Belum ada riwayat pembayaran untuk tagihan ini.
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        @php
                            $studentBills = $bill->bills->where('student_id', $student->id);
                            $hasSpecificMonthBills = $studentBills->filter(fn($b) => (int)$b->month >= 1 && (int)$b->month <= 12)->isNotEmpty();
                            $monthsToLoop = $hasSpecificMonthBills ? array_merge(range(7, 12), range(1, 6)) : $studentBills->pluck('month')->unique()->values()->all();
                            if (empty($monthsToLoop)) {
                                $monthsToLoop = [0];
                            }
                        @endphp
                        @foreach ($monthsToLoop as $month)
                        @php
                            $billDetail = $studentBills->firstWhere('month', $month) ?? ($month === 0 ? $studentBills->first() : null);
                            $amount = $billDetail ? $billDetail->amount : 0;
                            $paidAmountDetail = $billDetail ? $billDetail->paid_amount : 0;
                            $remainingAmount = $billDetail ? max(0, $amount - $paidAmountDetail) : 0;
                            $status = $billDetail ? $billDetail->status : 'UNPAID';
                            $isPaid = $billDetail && ($remainingAmount <= 0 && $amount > 0);
                            $detailPayment = $billDetail ? $billDetail->transactionDetails?->first()?->transaction : null;
                            
                            $modalId = "bayarLainnya{$bill->id}_{$month}";
                            $showModal = $billDetail && !$isPaid && $remainingAmount > 0;
                            
                            // Define classes based on status
                            $cardClass = $isPaid ? 'paid' : ($remainingAmount > 0 ? 'unpaid' : 'bg-secondary bg-opacity-10');
                            $textColor = $isPaid ? 'text-success' : ($remainingAmount > 0 ? 'text-warning' : 'text-muted');
                        @endphp
    
                        @if($billDetail)
                        <div class="col-12">
                            <div class="month-card rounded-3 p-3 px-md-4 {{ $cardClass }} {{ ($showModal && $canPayBill) ? 'cursor-pointer clickable-payment-card' : '' }}">
                                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                    
                                    <!-- Left side: Month & Year -->
                                    <div class="d-flex align-items-center gap-2" style="min-width: 150px;">
                                        <span class="fw-bold fs-6 text-slate-800">
                                            {{ ($month >= 1 && $month <= 12) ? \Carbon\Carbon::create()->month($month)->translatedFormat('F') : ($bill->name ?? 'Sekali Bayar') }}
                                        </span>
                                        <span class="badge badge-secondary fs-9 text-slate-600 fw-bold">
                                            {{ $billDetail->year ?? ($month >= 7 ? 
                                                ($bill->academicYear?->start_year ?? '-') : 
                                                ($bill->academicYear?->end_year ?? '-')) 
                                            }}
                                        </span>
                                    </div>
    
                                    <!-- Middle side: Nominal -->
                                    <div class="d-flex align-items-center" style="min-width: 130px;">
                                        @if($billDetail->paid_amount > 0 && !$isPaid)
                                            <div class="d-flex flex-column">
                                                <span class="fw-bolder fs-5 text-amber-600">
                                                    Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                                                </span>
                                                <span class="fs-9 text-slate-400">Sisa dari Rp {{ number_format($billDetail->amount, 0, ',', '.') }}</span>
                                            </div>
                                        @else
                                            <span class="fw-bolder fs-5 {{ $isPaid ? 'text-emerald-600' : ($remainingAmount > 0 ? 'text-amber-600' : 'text-slate-400') }}">
                                                @if($amount > 0)
                                                    Rp {{ number_format($isPaid ? ($billDetail->paid_amount ?: $billDetail->amount) : $remainingAmount, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @endif
                                    </div>
    
                                    @if($isPaid)
                                        <!-- Paid details: Transaksi Bayar, Metode Bayar, Nama Petugas -->
                                        <div class="d-flex flex-wrap gap-2 align-items-center text-slate-500 fs-9 flex-grow-1">
                                            <!-- Transaksi Bayar (Tanggal) -->
                                            @if(!empty($billDetail->paid_date))
                                                <span class="d-inline-flex align-items-center bg-white border border-gray-200 px-2.5 py-1 rounded text-slate-600 fw-bold">
                                                    <i class="fas fa-calendar-alt text-slate-400 me-1.5 fs-9"></i>
                                                    {{ date('d/m/y', strtotime($billDetail->paid_date)) }}
                                                </span>
                                            @endif
                                            
                                            <!-- Metode Bayar -->
                                            <span class="d-inline-flex align-items-center bg-white border border-gray-200 px-2.5 py-1 rounded text-slate-700 fw-bolder text-uppercase">
                                                {{ $billDetail->payment_method ?? '-' }}
                                            </span>
                                            
                                            <!-- Nama Petugas -->
                                            @if(strtoupper($billDetail->payment_method) == 'TUNAI' || strtoupper($billDetail->payment_method) == 'CASH' || !empty($detailPayment?->admin_id))
                                                <span class="d-inline-flex align-items-center bg-white border border-gray-200 px-2.5 py-1 rounded text-primary fw-bold">
                                                    <i class="fas fa-user-check text-primary me-1.5 fs-9"></i>
                                                    {{ $detailPayment->admin->name ?? 'Sistem / Admin' }}
                                                </span>
                                            @endif
                                        </div>
    
                                        <!-- Right side: Badge Lunas & Tombol Batal -->
                                        <div class="d-flex align-items-center ms-md-auto gap-2 flex-wrap">
                                            <span class="badge badge-success fw-bolder px-3 py-1.5 text-white">
                                                <i class="fas fa-check-circle me-1 text-white"></i> Lunas
                                            </span>
                                            @if(Auth::user()?->hasRole('Super Admin') || Auth::user()?->can('Cancel Tagihan') || Auth::user()?->can('Batal Transaksi Tagihan'))
                                                @if(!empty($billDetail?->id))
                                                <button type="button" 
                                                    class="btn btn-sm btn-light-danger fw-bold py-1 px-2.5 btn-cancel-bill"
                                                    title="Batalkan Pembayaran Tagihan Ini"
                                                    onclick="handleCancelBillPayment('{{ $billDetail->id }}', '{{ addslashes($bill->name) }}', event)">
                                                    <i class="fas fa-undo me-1 fs-8"></i> Batal
                                                </button>
                                                @endif
                                            @endif
                                        </div>
                                    @else
                                        <!-- Unpaid action: Klik Bayar -->
                                        <div class="d-flex align-items-center ms-md-auto">
                                            @if($showModal)
                                                @if($canPayBill)
                                                <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                    <input type="checkbox" 
                                                        name="bill_months[{{ $bill->id }}][]" 
                                                        value="{{ $month }}"
                                                        id="bill-other-{{ $bill->id }}-{{ $month }}"
                                                        class="form-check-input bill-month-checkbox bill-{{ $bill->id }} cursor-pointer" 
                                                        data-bill-id="{{ $billDetail->id }}"
                                                        data-month="{{ $billDetail->translated_month }}" 
                                                        data-year="{{ $billDetail->year }}"
                                                        data-bill-name="{{ $bill->name }}" 
                                                        data-amount="{{ $remainingAmount }}"
                                                        data-payment-input-type="{{ $bill->payment_input_type ?? 'FIXED' }}"
                                                        onclick="event.stopPropagation()">
                                                    <label class="form-check-label fw-bold text-slate-700 ms-2 fs-7 cursor-pointer" for="bill-other-{{ $bill->id }}-{{ $month }}" onclick="event.stopPropagation()">
                                                        Bayar
                                                    </label>
                                                </div>
                                                @else
                                                <span class="badge badge-light-warning text-warning fw-bold fs-8">Belum Lunas</span>
                                                @endif
                                            @else
                                                <span class="badge badge-light text-slate-400 fs-8">-</span>
                                            @endif
                                        </div>
                                    @endif
    
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
            </div>
        </div>
    @endforeach
    </div>
    @endif
</div>

@push('js')
<script>
    // Shortcut Generate button handler — tab Lainnya
    $(document).on('click', '.generate-lainnya-btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        var rateId = btn.data('rate-id');
        var studentId = btn.data('student-id');
        var btName = btn.data('bill-type-name');

        Swal.fire({
            title: 'Terbitkan Tagihan?',
            html: 'Sistem akan langsung menerbitkan tagihan <strong>' + btName + '</strong> untuk siswa ini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sync-alt me-1"></i> Ya, Terbitkan Sekarang',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-light'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');
                $.ajax({
                    url: "{{ route('payment-rate.generate-student') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_rate_id: rateId,
                        student_id: studentId
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Tagihan berhasil diterbitkan!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        // Reload halaman agar tagihan yang baru diterbitkan muncul di tab Lainnya
                        setTimeout(function() { location.reload(); }, 1500);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i>Terbitkan Sekarang');
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menerbitkan tagihan.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: msg
                        });
                    }
                });
            }
        });
    });
</script>
@endpush
