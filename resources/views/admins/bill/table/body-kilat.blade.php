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
    .form-check-custom .form-check-input {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
@endpush

<div class="accordion" id="accordionKilatParent">
    @foreach ($billMonth as $bill)
    @php
        $existingBills = $bill->bills->where('student_id', $student->id);
        $isZarkasi = str_contains(strtoupper($bill->name ?? ''), 'ZARKASI');
        $isAplikasi = str_contains(strtoupper($bill->name ?? ''), 'APLIKASI');
        
        $zarkasiTargets = [
            7  => 100000,
            8  => 100000,
            9  => 100000,
            10 => 100000,
            11 => 100000,
            12 => 50000,
        ];

        if ($isZarkasi) {
            $totalRawPaid = $existingBills->sum('paid_amount');
            $paidAmount = min(550000, $totalRawPaid);
            $unpaidAmount = max(0, 550000 - $paidAmount);

            $zarkasiPaidAllocated = [];
            $remPool = $totalRawPaid;
            foreach ([7, 8, 9, 10, 11, 12] as $m) {
                $t = $zarkasiTargets[$m];
                if ($remPool >= $t) {
                    $zarkasiPaidAllocated[$m] = $t;
                    $remPool -= $t;
                } else if ($remPool > 0) {
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
            
            // Find sample monthly amount from existing bills or billItem rate
            $sampleBill = $existingBills->firstWhere('amount', '>', 0);
            $sampleMonthlyAmount = $sampleBill ? $sampleBill->amount : ($bill->billItem->amount ?? 0);
            if ($sampleMonthlyAmount <= 0) {
                $sampleMonthlyAmount = \App\Models\Bill::where('bill_type_id', $bill->id)->where('amount', '>', 0)->value('amount') ?? 0;
            }

            // Calculate unpaid amount across all 12 months (existing DB rows + un-generated months)
            $unpaidAmount = 0;
            foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                $bDet = $existingBills->firstWhere('month', (int)$m);
                if (!$bDet) {
                    $bDet = $existingBills->firstWhere('month', (string)$m);
                }

                if ($bDet) {
                    $unpaidAmount += max(0, $bDet->amount - $bDet->paid_amount);
                } else {
                    $unpaidAmount += $sampleMonthlyAmount;
                }
            }
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
                        <div class="px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm text-nowrap" 
                             style="background: {{ $currentAyTheme['bg'] }}; color: #ffffff; border-radius: 20px; box-shadow: 0 3px 10px {{ $currentAyTheme['shadow'] }};">
                            <i class="fas fa-calendar-alt text-white fs-8 me-0.5 opacity-90"></i>
                            <span class="fs-7 fw-bold text-white tracking-tight" style="letter-spacing: -0.2px;">
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
                            $amount = $billDetail ? $billDetail->amount : $sampleMonthlyAmount;
                            $remainingAmount = $billDetail ? max(0, $billDetail->amount - $billDetail->paid_amount) : $amount;
                            $status = $billDetail ? $billDetail->status : ($amount > 0 ? 'UNPAID' : 'FREE');
                            $isPaid = $status == 'PAID' || ($billDetail && $remainingAmount <= 0 && $amount > 0);
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
                                        @if(strtoupper($billDetail?->payment_method ?? '') == 'TUNAI' || strtoupper($billDetail?->payment_method ?? '') == 'CASH')
                                            <div class="text-primary fw-bold fs-9">
                                                <i class="fas fa-user-check me-1"></i>
                                                {{ $detailPayment->admin->name ?? $detailPayment->user->name ?? 'Admin' }}
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
</script>
@endpush