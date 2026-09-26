@push('css')
<style>
    .payment-card {
        background-color: #f5f5f5;
        /* Light gray background */
        border: none;
        /* Remove border */
        border-bottom: 1px solid #dcdcdc;
        /* Add bottom border for separation */
        margin-bottom: 0;
        /* Remove bottom margin */
        padding: 2px;
        /* Add some padding for better readability */
    }

    #payment-details .payment-card:last-child {
        border-bottom: none;
        /* Remove bottom border for the last card */
    }
</style>
@endpush
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
        background-color: #f0fdf4; /* Very light emerald */
        border-color: #10b981; /* Solid Emerald-500 */
    }
    .month-card.unpaid {
        background-color: #fffbeb; /* Very light yellow */
        border-color: #f59e0b; /* Solid Amber-500 */
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

<div class="accordion" id="accordionKilatParent">
    @if(isset($ungeneratedMonthlyRates) && $ungeneratedMonthlyRates->isNotEmpty())
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
                @foreach($ungeneratedMonthlyRates as $ur)
                <div class="d-flex align-items-center bg-white rounded-3 px-4 py-3" style="box-shadow: 0 2px 8px rgba(0,0,0,0.04); min-width: 260px;">
                    <div class="flex-grow-1 me-3">
                        <div class="fw-bold text-gray-800 fs-6">{{ $ur->bill_type_name }}</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge badge-light-primary fs-8">{{ $ur->academic_year_name }}</span>
                            @if($ur->amount > 0)
                            <span class="fw-semibold text-emerald-600 fs-7" style="color: #10B981;">Rp {{ number_format($ur->amount, 0, ',', '.') }} /bln</span>
                            @endif
                        </div>
                    </div>
                    <button type="button"
                        class="btn btn-sm btn-primary generate-bulanan-btn hover-scale"
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

    @if($billMonth->isEmpty() && (!isset($ungeneratedMonthlyRates) || $ungeneratedMonthlyRates->isEmpty()))
    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 my-4">
        <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
            <i class="fas fa-exclamation-triangle fs-1 text-warning"></i>
        </span>
        <div class="d-flex flex-stack flex-grow-1">
            <div class="fw-bold">
                <h4 class="text-gray-900 fw-bolder">Belum Ada Tagihan Bulanan yang Di-generate</h4>
                <div class="fs-6 text-gray-700">
                    Tagihan kategori <strong>Bulanan</strong> (Syahriah, SPP, Aplikasi, dll) belum dibuat/di-generate untuk siswa <strong>{{ $student->name }}</strong>.
                    <br>
                    <span class="text-muted fs-7 mt-2 d-inline-block">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        <strong>Panduan Admin:</strong> Silakan masuk ke menu <a href="{{ route('bill-type.index') }}" class="fw-bolder text-primary">Data Jenis Bayar</a>, lalu klik tombol sinkronisasi <i class="fas fa-sync text-success me-1"></i> <strong>Generasi Tagihan</strong> pada kelas siswa ini ({{ $displayClassName }}).
                    </span>

                        <form action="{{ route('bill.generate-student-bills', ['student_id' => $student->id, 'academic_year_id' => request('academic_year_id')]) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-sync-alt me-1"></i> Generate Bill Bulanan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
    @foreach ($billMonth as $bill)
    @php
        $isZarkasi = str_contains(strtoupper($bill->name ?? ''), 'ZARKASI');
        $isAplikasi = str_contains(strtoupper($bill->name ?? ''), 'APLIKASI');

        if (isset($allStudentBills) && $allStudentBills) {
            if ($isZarkasi) {
                $existingBills = $allStudentBills->filter(fn($b) => ($b->academic_year_id == $bill->academic_year_id || $b->billType?->academic_year_id == $bill->academic_year_id) && is_null($b->billType?->deleted_at) && str_contains(strtoupper($b->billType?->name ?? ''), 'ZARKASI'));
            } elseif ($isAplikasi) {
                $existingBills = $allStudentBills->filter(fn($b) => ($b->academic_year_id == $bill->academic_year_id || $b->billType?->academic_year_id == $bill->academic_year_id) && is_null($b->billType?->deleted_at) && str_contains(strtoupper($b->billType?->name ?? ''), 'APLIKASI'));
            } else {
                $existingBills = $allStudentBills->where('bill_type_id', $bill->id);
            }
        } else {
            if ($isZarkasi) {
                $existingBills = \App\Models\Bill::where('student_id', $student->id)
                    ->where('academic_year_id', $bill->academic_year_id)
                    ->whereHas('billType', fn($q) => $q->whereNull('deleted_at')->where('name', 'like', '%ZARKASI%'))
                    ->get();
            } elseif ($isAplikasi) {
                $existingBills = \App\Models\Bill::where('student_id', $student->id)
                    ->where('academic_year_id', $bill->academic_year_id)
                    ->whereHas('billType', fn($q) => $q->whereNull('deleted_at')->where('name', 'like', '%APLIKASI%'))
                    ->get();
            } else {
                $existingBills = \App\Models\Bill::where('student_id', $student->id)
                    ->where('bill_type_id', $bill->id)
                    ->whereNull('deleted_at')
                    ->get();
            }
        }
        
        // Build Zarkasi targets dynamically dari data tagihan aktual di DB
        // (sebelumnya hardcoded 100k×5 + 50k = 550k yang hanya cocok untuk kelas 12)
        $zarkasiTargets = [];
        if ($isZarkasi) {
            foreach (array_merge(range(7, 12), range(1, 6)) as $zm) {
                $zBill = $existingBills->firstWhere('month', (int)$zm) ?? $existingBills->firstWhere('month', (string)$zm);
                $zarkasiTargets[$zm] = $zBill ? (int)$zBill->amount : 0;
            }
        }

        if ($isZarkasi) {
            $zarkasiTotal = array_sum($zarkasiTargets);
            $totalRawPaid = $existingBills->sum('paid_amount');
            $paidAmount = min($zarkasiTotal, $totalRawPaid);
            $unpaidAmount = max(0, $zarkasiTotal - $paidAmount);

            $zarkasiPaidAllocated = [];
            $remPool = $totalRawPaid;
            foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                $t = $zarkasiTargets[$m] ?? 0;
                if ($remPool >= $t && $t > 0) {
                    $zarkasiPaidAllocated[$m] = $t;
                    $remPool -= $t;
                } else if ($remPool > 0 && $t > 0) {
                    $zarkasiPaidAllocated[$m] = $remPool;
                    $remPool = 0;
                } else {
                    $zarkasiPaidAllocated[$m] = 0;
                }
            }
        } elseif ($isAplikasi) {
            $totalRawPaid = $existingBills->sum('paid_amount');
            $paidAmount = min(120000, $totalRawPaid);
            $unpaidAmount = max(0, 120000 - $paidAmount);

            $aplikasiPaidAllocated = [];
            $remPool = $totalRawPaid;
            foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                $t = 10000;
                if ($remPool >= $t) {
                    $aplikasiPaidAllocated[$m] = $t;
                    $remPool -= $t;
                } else if ($remPool > 0) {
                    $aplikasiPaidAllocated[$m] = $remPool;
                    $remPool = 0;
                } else {
                    $aplikasiPaidAllocated[$m] = 0;
                }
            }
        } else {
            $paidAmount = $existingBills->sum('paid_amount');
            $startYear = $bill->academicYear?->start_year ?? date('Y');
            $endYear = $bill->academicYear?->end_year ?? ($startYear + 1);

            $totalBillAmount = 0;
            foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                $y = ($m >= 7) ? $startYear : $endYear;
                $bDet = $existingBills->firstWhere('month', (int)$m) ?? $existingBills->firstWhere('month', (string)$m);

                if ($bDet !== null) {
                    $totalBillAmount += $bDet->amount;
                } else {
                    $totalBillAmount += \App\Services\TransactionService::resolveStudentRateForBillType($student, $bill, $m, $y, $preloadedRates ?? null);
                }
            }
            $unpaidAmount = max(0, $totalBillAmount - $paidAmount);
        }

        // Hide Rp 0 / Rp 0 dummy bill cards (e.g. REGISTRASI for senior classes), unless they have an active rate
        $hasActiveRate = \App\Services\TransactionService::hasActiveRateForStudent($student, $bill, $preloadedRates ?? null);
        if (!$hasActiveRate && ($totalBillAmount ?? $paidAmount) == 0 && $paidAmount == 0 && $unpaidAmount == 0) {
            continue;
        }

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
    
    <div class="accordion-item mb-5 border border-gray-300 shadow-sm rounded-3 overflow-hidden">
        <h2 class="accordion-header" id="headingKilat{{ $bill->id }}">
            <button class="accordion-button fs-4 fw-bold collapsed bg-light text-dark d-block" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseKilat{{ $bill->id }}" 
                aria-expanded="false" 
                aria-controls="collapseKilat{{ $bill->id }}">
                
                <div class="row w-100 align-items-center pe-3">
                    <!-- Left: Title & Category -->
                     <div class="col-md-4 col-12 d-flex flex-column justify-content-center text-start">
                          <div class="d-flex align-items-center flex-wrap gap-2">
                              <span class="text-slate-900 fs-5 fw-bolder me-1">{{ $bill->name }}</span>
                              <span class="badge badge-primary fw-bold fs-8 px-3 py-1">Bulanan</span>
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
                         <div class="d-flex flex-column align-items-start align-items-md-end border-start border-gray-300 ps-3 ms-1">
                             <span class="fs-8 text-slate-500 fw-bold text-uppercase mb-1">Sisa Tagihan</span>
                             <span class="badge badge-danger fs-7 fw-bolder px-3 py-1 text-white">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</span>
                         </div>
                         
                         <div class="d-none d-md-block ms-2 text-slate-400 fs-8 fw-bold">
                            Lihat Rincian
                         </div>
                    </div>
                </div>
            </button>
        </h2>

        <div id="collapseKilat{{ $bill->id }}" class="accordion-collapse collapse" aria-labelledby="headingKilat{{ $bill->id }}">
            <div class="accordion-body bg-white border-top p-4 p-md-5">
                <div class="row g-3">
                    @foreach (array_merge(range(7, 12), range(1, 6)) as $month)
                    @php
                        $billDetail = $existingBills->firstWhere('month', (int)$month);
                        if (!$billDetail) {
                            $billDetail = $existingBills->firstWhere('month', (string)$month);
                        }

                        if ($isZarkasi) {
                            $amount = $zarkasiTargets[$month] ?? 0;
                            $mPaid = $zarkasiPaidAllocated[$month] ?? 0;
                            $remainingAmount = max(0, $amount - $mPaid);
                            $isPaid = ($amount > 0) && ($remainingAmount == 0);
                            $status = $isPaid ? 'PAID' : ($amount > 0 ? 'UNPAID' : 'FREE');
                            $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
                        } elseif ($isAplikasi) {
                            $amount = 10000;
                            $mPaid = $aplikasiPaidAllocated[$month] ?? 0;
                            $remainingAmount = max(0, $amount - $mPaid);
                            $isPaid = ($amount > 0) && ($remainingAmount == 0);
                            $status = $isPaid ? 'PAID' : 'UNPAID';
                            $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
                        } else {
                            $targetYearTemp = $billDetail?->year ?? ($month >= 7 ? ($bill->academicYear?->start_year ?? date('Y')) : ($bill->academicYear?->end_year ?? (date('Y') + 1)));
                            $amount = ($billDetail !== null) ? $billDetail->amount : \App\Services\TransactionService::resolveStudentRateForBillType($student, $bill, $month, $targetYearTemp, $preloadedRates ?? null);
                            
                            $isPaid = ($billDetail && $billDetail->status == 'PAID') || ($unpaidAmount == 0 && $paidAmount >= ($totalBillAmount ?? 0) && $amount > 0);
                            $remainingAmount = $isPaid ? 0 : ($billDetail ? max(0, $billDetail->amount - $billDetail->paid_amount) : $amount);
                            $status = $isPaid ? 'PAID' : ($amount > 0 ? 'UNPAID' : 'FREE');
                            $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
                        }

                        $modalId = "bayarKilat{$bill->id}_{$month}";
                        $showModal = !$isPaid && $remainingAmount > 0 && $amount > 0;
                        
                        $targetYear = $billDetail?->year ?? ($month >= 7 ? 
                            ($bill->academicYear?->start_year ?? date('Y')) : 
                            ($bill->academicYear?->end_year ?? (date('Y') + 1)));

                        // Define classes based on status
                        $cardClass = $isPaid ? 'paid' : ($remainingAmount > 0 ? 'unpaid' : 'bg-secondary bg-opacity-10');
                        $textColor = $isPaid ? 'text-success' : ($remainingAmount > 0 ? 'text-warning' : 'text-muted');
                        
                        $autoBillId = $billDetail ? $billDetail->id : "auto_{$bill->id}_{$month}_{$targetYear}";
                    @endphp

                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="month-card rounded-3 p-3 h-100 d-flex flex-column justify-content-between position-relative {{ $cardClass }} {{ $showModal ? 'cursor-pointer clickable-payment-card' : '' }}">
                            <!-- Header: Month & Year -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold fs-7 text-slate-800">
                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                </span>
                                <span class="badge badge-secondary fs-9 text-slate-600 fw-bold">
                                    {{ $targetYear }}
                                </span>
                            </div>

                            <!-- Body: Amount -->
                            <div class="text-center my-2">
                                @if($billDetail && $billDetail->paid_amount > 0 && !$isPaid)
                                    <span class="fw-bolder fs-5 text-amber-600">
                                        Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                                    </span>
                                    <div class="fs-9 text-slate-400">Sisa dari Rp {{ number_format($billDetail->amount, 0, ',', '.') }}</div>
                                @else
                                    <span class="fw-bolder fs-5 {{ $isPaid ? 'text-emerald-600' : ($remainingAmount > 0 ? 'text-amber-600' : 'text-slate-400') }}">
                                        Rp {{ number_format($isPaid ? ($billDetail?->paid_amount ?: ($billDetail?->amount ?? $amount)) : $remainingAmount, 0, ',', '.') }}
                                    </span>
                                @endif
                                @if($isPaid && $detailPayment)
                                    <div class="fs-9 text-slate-500 mt-2 pt-2 border-top border-gray-200">
                                        <div class="d-flex justify-content-center align-items-center mb-1 fw-bold">
                                            <i class="fas fa-calendar-alt me-1 fs-9"></i>
                                            {{ !empty($billDetail?->paid_date) ? date('d/m/y', strtotime($billDetail->paid_date)) : '-' }}
                                        </div>
                                        <div class="fw-bolder text-slate-700">{{ $billDetail?->payment_method ?? '-' }}</div>
                                        @if(strtoupper($billDetail?->payment_method ?? '') == 'TUNAI' || strtoupper($billDetail?->payment_method ?? '') == 'CASH' || !empty($detailPayment?->admin_id))
                                            <div class="text-primary fw-bold fs-9">
                                                <i class="fas fa-user-check me-1"></i>
                                                {{ $detailPayment->admin->name ?? 'Sistem / Admin' }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Footer: Action/Status -->
                            <div class="mt-2 d-flex justify-content-center align-items-center">
                                @if($amount == 0)
                                    <span class="badge badge-light text-slate-400 fs-9 fw-bold">Rp 0 (Bebas)</span>
                                @elseif($isPaid)
                                    <span class="badge badge-success fw-bolder px-3 py-1 text-white">
                                        <i class="fas fa-check-circle me-1 text-white"></i> Lunas
                                    </span>
                                @elseif($showModal)
                                    <div class="form-check form-check-custom form-check-solid form-check-sm">
                                        <input type="checkbox" 
                                            name="bill_months[{{ $bill->id }}][]" 
                                            value="{{ $month }}"
                                            id="bill-month-{{ $bill->id }}-{{ $month }}"
                                            class="form-check-input bill-month-checkbox bill-{{ $bill->id }} cursor-pointer" 
                                            data-bill-id="{{ $autoBillId }}"
                                            data-month="{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}" 
                                            data-year="{{ $targetYear }}"
                                            data-bill-name="{{ $bill->name }}" 
                                            data-amount="{{ $remainingAmount }}"
                                            data-payment-input-type="{{ $bill->payment_input_type ?? 'FIXED' }}"
                                            onclick="event.stopPropagation()">
                                        <label class="form-check-label fw-bold text-slate-700 ms-2 fs-7 cursor-pointer" for="bill-month-{{ $bill->id }}-{{ $month }}" onclick="event.stopPropagation()">
                                            Bayar
                                        </label>
                                    </div>
                                @else
                                    <span class="badge badge-light text-slate-400 fs-8">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');

        // Update card visual selection state based on its checkbox
        function updateCardSelectionStates() {
            const checkboxes = document.querySelectorAll('.bill-month-checkbox');
            checkboxes.forEach(checkbox => {
                const card = checkbox.closest('.month-card');
                if (card) {
                    if (checkbox.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                }
            });
        }

        // Toggle checkbox when clicking the card itself
        const clickableCards = document.querySelectorAll('.month-card.clickable-payment-card');
        clickableCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // If the user clicked directly on the checkbox or its label, let the default browser behavior handle it
                if (e.target.closest('.form-check')) {
                    return;
                }
                const checkbox = card.querySelector('.bill-month-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });

        // Sync selectAllCheckbox state and card selected classes on checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('bill-month-checkbox')) {
                const card = e.target.closest('.month-card');
                if (card) {
                    card.classList.toggle('selected', e.target.checked);
                }

                // Sync "Bayar Semua" checkbox state
                const allCheckboxes = document.querySelectorAll('.bill-month-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.bill-month-checkbox:checked');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = (allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length);
                }
            }
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const billMonthCheckboxes = document.querySelectorAll('.bill-month-checkbox');
                billMonthCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateCardSelectionStates();
            });
        }

        // Prevent the modal from opening when clicking on checkboxes (redundant since we stopPropagation, but good fallback)
        const preventModalCheckboxes = document.querySelectorAll('.prevent-modal');
        preventModalCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });

        // Handle "Bayar" button click
        // Handle "Bayar" button click
        const modalPayBtn = document.querySelector('.modal-pay');
        if (modalPayBtn) {
            modalPayBtn.addEventListener('click', function() {
                const selectedCheckboxes = document.querySelectorAll('.bill-month-checkbox:checked');
                const paymentDetails = document.getElementById('payment-details');
                const totalAmountElement = document.getElementById('total-amount');

                paymentDetails.innerHTML = ''; // Clear previous details

                // Remove any existing bill_ids hidden inputs
                document.querySelectorAll('input[name="bill_ids[]"]').forEach(input => input.remove());

                // Reset payment option to default LUNAS
                const paymentOption = document.getElementById('payment-option');
                if (paymentOption) {
                    paymentOption.value = 'LUNAS';
                }

                selectedCheckboxes.forEach(checkbox => {
                    const billId = checkbox.getAttribute('data-bill-id');
                    const billName = checkbox.getAttribute('data-bill-name');
                    const translatedMonth = checkbox.getAttribute('data-month');
                    const year = checkbox.getAttribute('data-year');
                    const amount = parseInt(checkbox.getAttribute('data-amount'));
                    const inputType = checkbox.getAttribute('data-payment-input-type') || 'FIXED';

                    if (!isNaN(amount)) {
                        // Create a new hidden input for each selected bill ID
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'bill_ids[]';
                        hiddenInput.value = billId;
                        document.getElementById('form-multi-payment').appendChild(hiddenInput);

                        // Create a new col-md-6 wrapper for 2 columns layout
                        const colDiv = document.createElement('div');
                        colDiv.className = 'col-md-6 mb-3';

                        if (inputType === 'FREE') {
                            colDiv.innerHTML = `
                                <div class="card h-100 border border-gray-200 shadow-none" style="border-radius: 16px; background-color: #f8fafc;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex flex-column text-start">
                                                <span class="fw-bold fs-6 text-slate-800">${billName}</span>
                                                <span class="text-slate-500 fs-7 mt-1">${translatedMonth} ${year}</span>
                                            </div>
                                            <span class="badge badge-light-warning fw-bolder fs-9">Cicilan</span>
                                        </div>
                                        <div class="mt-2 text-start">
                                            <label class="fs-9 text-slate-500 fw-bold text-uppercase">Jumlah Bayar (Sisa: Rp ${amount.toLocaleString('id-ID')})</label>
                                            <div class="input-group input-group-sm mt-1">
                                                <span class="input-group-text bg-white border-gray-300 text-slate-600">Rp</span>
                                                 <input type="text" class="form-control border-gray-300 custom-amount-input input-money" 
                                                     name="custom_amounts[${billId}]" 
                                                     value="${amount.toLocaleString('id-ID')}" 
                                                     data-bill-id="${billId}" 
                                                     data-max-amount="${amount}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Create companion card for Sisa Angsuran
                            const companionDiv = document.createElement('div');
                            companionDiv.className = 'col-md-6 mb-3 sisa-angsuran-card';
                            companionDiv.setAttribute('data-companion-bill-id', billId);
                            companionDiv.style.display = 'none'; // hidden by default since LUNAS is default
                            companionDiv.innerHTML = `
                                <div class="card h-100 border border-success border-opacity-20 shadow-none" style="border-radius: 16px; background-color: #f0fdf4;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center align-items-center text-center">
                                        <span class="text-slate-500 fs-9 fw-bold text-uppercase tracking-wider mb-1">Sisa Angsuran</span>
                                        <span class="text-emerald-600 fw-boldest fs-3" id="sisa-amount-${billId}">Rp 0</span>
                                    </div>
                                </div>
                            `;

                            paymentDetails.appendChild(colDiv);
                            paymentDetails.appendChild(companionDiv);
                        } else {
                            colDiv.innerHTML = `
                                <div class="card h-100 border border-gray-200 shadow-none" style="border-radius: 16px; background-color: #f8fafc;">
                                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                        <div class="d-flex flex-column text-start">
                                            <span class="fw-bold fs-6 text-slate-800">${billName}</span>
                                            <span class="text-slate-500 fs-7 mt-1">${translatedMonth} ${year}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-boldest fs-6 text-slate-900">Rp ${amount.toLocaleString('id-ID')}</span>
                                            <input type="hidden" name="custom_amounts[${billId}]" value="${amount}">
                                        </div>
                                    </div>
                                </div>
                            `;
                            paymentDetails.appendChild(colDiv);
                        }
                    }
                });

                // Apply initial state behavior (Lunas by default)
                updatePaymentOptionBehavior();
            });
        }

        // Behavior control for Lunas / Angsur
        function updatePaymentOptionBehavior() {
            const paymentOption = document.getElementById('payment-option');
            const isAngsur = paymentOption && paymentOption.value === 'ANGSUR';
            
            const customInputs = document.querySelectorAll('.custom-amount-input');
            customInputs.forEach(input => {
                const billId = input.getAttribute('data-bill-id');
                const maxAmount = parseInt(input.getAttribute('data-max-amount'));
                const companionCard = document.querySelector(`.sisa-angsuran-card[data-companion-bill-id="${billId}"]`);
                
                if (isAngsur) {
                    input.removeAttribute('readonly');
                    input.classList.remove('bg-light');
                    if (companionCard) {
                        companionCard.style.display = 'block';
                        // Update the dynamic text
                        let val = parseInt(input.value.replace(/\D/g, ''));
                        if (isNaN(val) || val < 0) val = 0;
                        const sisa = Math.max(0, maxAmount - val);
                        const sisaAmountEl = document.getElementById(`sisa-amount-${billId}`);
                        if (sisaAmountEl) {
                            sisaAmountEl.textContent = `Rp ${sisa.toLocaleString('id-ID')}`;
                        }
                    }
                } else {
                    input.setAttribute('readonly', 'readonly');
                    input.classList.add('bg-light');
                    input.value = maxAmount.toLocaleString('id-ID'); // Force to full amount
                    if (companionCard) {
                        companionCard.style.display = 'none';
                    }
                }
            });
            
            calculateTotal();
        }

        // Listen for payment option changes
        const paymentOption = document.getElementById('payment-option');
        if (paymentOption) {
            paymentOption.addEventListener('change', updatePaymentOptionBehavior);
        }

        // Recalculate and update interface
        function calculateTotal() {
            const selectedCheckboxes = document.querySelectorAll('.bill-month-checkbox:checked');
            const totalAmountElement = document.getElementById('total-amount');
            let total = 0;

            selectedCheckboxes.forEach(checkbox => {
                const billId = checkbox.getAttribute('data-bill-id');
                const inputType = checkbox.getAttribute('data-payment-input-type') || 'FIXED';
                const defaultAmount = parseInt(checkbox.getAttribute('data-amount'));

                if (inputType === 'FREE') {
                    const input = document.querySelector(`.custom-amount-input[data-bill-id="${billId}"]`);
                    let amt = input ? parseInt(input.value.replace(/\D/g, '')) : defaultAmount;
                    if (isNaN(amt) || amt < 1) amt = 0;
                    total += amt;
                } else {
                    total += defaultAmount;
                }
            });

            if (totalAmountElement) {
                totalAmountElement.textContent = `Rp ${total.toLocaleString('id-ID')}`;
            }

            const studentBalance = parseInt('{{ $student->saldo }}');
            const paymentMethod = document.getElementById('payment-method');
            if (paymentMethod) {
                const balanceOption = paymentMethod.querySelector('option[value="BALANCE"]');
                if (balanceOption) {
                    if (studentBalance < total) {
                        balanceOption.style.display = 'none';
                        balanceOption.disabled = true;
                        if (paymentMethod.value === 'BALANCE') {
                            paymentMethod.value = '';
                        }
                    } else {
                        balanceOption.style.display = 'block';
                        balanceOption.disabled = false;
                    }
                }
            }
        }

        // Add real-time event listener for custom input changes
        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('custom-amount-input')) {
                const maxAmt = parseInt(e.target.getAttribute('data-max-amount'));
                let val = parseInt(e.target.value.replace(/\D/g, ''));
                if (isNaN(val) || val < 1) {
                    val = 0;
                } else if (val > maxAmt) {
                    e.target.value = maxAmt.toLocaleString('id-ID');
                    val = maxAmt;
                }
                
                // Update dynamic remaining amount for the card
                const billId = e.target.getAttribute('data-bill-id');
                const companionCard = document.querySelector(`.sisa-angsuran-card[data-companion-bill-id="${billId}"]`);
                if (companionCard) {
                    const sisa = Math.max(0, maxAmt - val);
                    const sisaAmountEl = document.getElementById(`sisa-amount-${billId}`);
                    if (sisaAmountEl) {
                        sisaAmountEl.textContent = `Rp ${sisa.toLocaleString('id-ID')}`;
                    }
                }

                calculateTotal();
            }
        });

        document.addEventListener('blur', function(e) {
            if (e.target && e.target.classList.contains('custom-amount-input')) {
                const maxAmt = parseInt(e.target.getAttribute('data-max-amount'));
                let val = parseInt(e.target.value.replace(/\D/g, ''));
                if (isNaN(val) || val < 1) {
                    e.target.value = 1;
                    val = 1;
                } else if (val > maxAmt) {
                    e.target.value = maxAmt.toLocaleString('id-ID');
                    val = maxAmt;
                }

                // Update companion card
                const billId = e.target.getAttribute('data-bill-id');
                const companionCard = document.querySelector(`.sisa-angsuran-card[data-companion-bill-id="${billId}"]`);
                if (companionCard) {
                    const sisa = Math.max(0, maxAmt - val);
                    const sisaAmountEl = document.getElementById(`sisa-amount-${billId}`);
                    if (sisaAmountEl) {
                        sisaAmountEl.textContent = `Rp ${sisa.toLocaleString('id-ID')}`;
                    }
                }

                calculateTotal();
            }
        }, true);
    });

    // Shortcut Generate button handler — tab Bulanan (Kilat)
    $(document).on('click', '.generate-bulanan-btn', function(e) {
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
                        // Reload halaman agar tagihan yang baru diterbitkan muncul di tab Bulanan
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