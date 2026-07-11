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
    .form-check-custom .form-check-input {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
@endpush

<div class="accordion" id="accordionLainnyaParent">
    <div class="row g-4">
        @foreach ($billOthers as $bill)
        @php
            $paidAmount = $bill->bills->where('student_id', $student->id)->where('status', 'PAID')->sum('amount');
            $unpaidAmount = $bill->total_unpaid;
        @endphp
        <div class="col-md-6">
            <div class="accordion-item mb-5 border border-gray-300 shadow-sm rounded-3 overflow-hidden">
        <h2 class="accordion-header" id="headingLainnya{{ $bill->id }}">
            <button class="accordion-button fs-4 fw-bold collapsed bg-light text-dark d-block" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseLainnya{{ $bill->id }}" 
                aria-expanded="false" 
                aria-controls="collapseLainnya{{ $bill->id }}">
                
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center w-100 gap-3 pe-3">
                    <!-- Left: Title & Year -->
                    <div class="d-flex flex-column text-start">
                         <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                             <span class="text-slate-900 fs-5 fw-bolder">{{ $bill->name }}</span>
                             <span class="badge badge-warning fw-bold fs-8 px-3 py-1 text-white">Tagihan Lain</span>
                         </div>
                         <span class="text-slate-500 fs-7 fw-bold">
                            <i class="fas fa-calendar-alt me-1 text-slate-400 fs-8"></i>
                            Tahun Ajaran {{ $bill->academicYear->name ?? '-' }}
                         </span>
                    </div>

                    <!-- Right: Stats & Action -->
                    <div class="d-flex align-items-center gap-3 mt-2 mt-sm-0">
                         <!-- Paid Stat -->
                         <div class="d-flex flex-column align-items-start align-items-sm-end">
                             <span class="fs-8 text-slate-500 fw-bold text-uppercase mb-1">Terbayar</span>
                             <span class="badge badge-success fs-7 fw-bolder px-3 py-1 text-white">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                         </div>
 
                         <!-- Unpaid Stat -->
                         <div class="d-flex flex-column align-items-start align-items-sm-end border-start border-gray-300 ps-3">
                             <span class="fs-8 text-slate-500 fw-bold text-uppercase mb-1">Sisa Tagihan</span>
                             <span class="badge badge-danger fs-7 fw-bolder px-3 py-1 text-white">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</span>
                         </div>
                         
                         <div class="d-none d-sm-block ms-1 text-slate-400 fs-8 fw-bold">
                            Lihat Rincian
                         </div>
                    </div>
                </div>
            </button>
        </h2>

        <div id="collapseLainnya{{ $bill->id }}" class="accordion-collapse collapse" aria-labelledby="headingLainnya{{ $bill->id }}">
            <div class="accordion-body bg-white border-top p-4 p-md-5">
                <div class="row g-3">
                    @foreach (array_merge(range(7, 12), range(1, 6)) as $month)
                    @php
                        $billDetail = $bill->bills->where('month', $month)->where('student_id', $student->id)->first();
                        $amount = $billDetail ? $billDetail->amount : 0;
                        $remainingAmount = $billDetail ? ($billDetail->amount - $billDetail->paid_amount) : 0;
                        $status = $billDetail ? $billDetail->status : 'UNPAID';
                        $isPaid = $status == 'PAID' || ($billDetail && $remainingAmount <= 0 && $amount > 0);
                        $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
                        
                        $modalId = "bayarLainnya{$bill->id}_{$month}";
                        $showModal = $billDetail && !$isPaid && $remainingAmount > 0;
                        
                        // Define classes based on status
                        $cardClass = $isPaid ? 'paid' : ($remainingAmount > 0 ? 'unpaid' : 'bg-secondary bg-opacity-10');
                        $textColor = $isPaid ? 'text-success' : ($remainingAmount > 0 ? 'text-warning' : 'text-muted');
                    @endphp

                    @if($billDetail)
                    <div class="col-12">
                        <div class="month-card rounded-3 p-3 px-md-4 {{ $cardClass }} {{ $showModal ? 'cursor-pointer clickable-payment-card' : '' }}">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                
                                <!-- Left side: Month & Year -->
                                <div class="d-flex align-items-center gap-2" style="min-width: 150px;">
                                    <span class="fw-bold fs-6 text-slate-800">
                                        {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                    </span>
                                    <span class="badge badge-secondary fs-9 text-slate-600 fw-bold">
                                        {{ $billDetail->year ?? ($month >= 7 ? 
                                            ($bill->academicYear->start_year ?? '-') : 
                                            ($bill->academicYear->end_year ?? '-')) 
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
                                        @if(strtoupper($billDetail->payment_method) == 'TUNAI' || strtoupper($billDetail->payment_method) == 'CASH')
                                            <span class="d-inline-flex align-items-center bg-white border border-gray-200 px-2.5 py-1 rounded text-primary fw-bold">
                                                <i class="fas fa-user-check text-primary me-1.5 fs-9"></i>
                                                {{ $detailPayment->admin->name ?? $detailPayment->user->name ?? 'Admin' }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Right side: Badge Lunas -->
                                    <div class="d-flex align-items-center ms-md-auto">
                                        <span class="badge badge-success fw-bolder px-3 py-1.5 text-white">
                                            <i class="fas fa-check-circle me-1 text-white"></i> Lunas
                                        </span>
                                    </div>
                                @else
                                    <!-- Unpaid action: Klik Bayar -->
                                    <div class="d-flex align-items-center ms-md-auto">
                                        @if($showModal)
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
            </div>
            </div>
        </div>
    </div>
    @endforeach
    </div>
</div>
