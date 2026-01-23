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
        background-color: #e8fff3;
        border-color: #50cd89;
    }
    .month-card.unpaid {
        background-color: #fff8dd;
        border-color: #ffc700;
    }
    .form-check-custom .form-check-input {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
@endpush

<div class="accordion" id="accordionLainnyaParent">
    @foreach ($billOthers as $bill)
    @php
        $paidAmount = $bill->bills->where('student_id', $student->id)->where('status', 'PAID')->sum('amount');
        $unpaidAmount = $bill->total_unpaid;
    @endphp
    
    <div class="accordion-item mb-5 border border-gray-300 shadow-sm rounded-3 overflow-hidden">
        <h2 class="accordion-header" id="headingLainnya{{ $bill->id }}">
            <button class="accordion-button fs-4 fw-bold collapsed bg-light text-dark d-block" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseLainnya{{ $bill->id }}" 
                aria-expanded="false" 
                aria-controls="collapseLainnya{{ $bill->id }}">
                
                <div class="row w-100 align-items-center pe-3">
                    <!-- Left: Title & Year -->
                    <div class="col-md-6 d-flex flex-column text-start">
                         <div class="d-flex align-items-center mb-1">
                             <span class="text-dark fs-5 fw-bolder me-2">{{ $bill->name }}</span>
                             <span class="badge badge-light-warning fw-bold fs-8">Tagihan Lain</span>
                         </div>
                         <span class="text-muted fs-7 fw-semibold">
                            <i class="fas fa-calendar-alt me-1 text-muted fs-8"></i>
                            Tahun Ajaran {{ $bill->academicYear->name ?? '-' }}
                         </span>
                    </div>

                    <!-- Right: Stats & Action -->
                    <div class="col-md-6 d-flex justify-content-md-end align-items-center mt-3 mt-md-0 gap-2 gap-md-4">
                         <!-- Paid Stat -->
                         <div class="d-flex flex-column align-items-start align-items-md-end">
                             <span class="fs-8 text-muted fw-bold text-uppercase mb-1">Terbayar</span>
                             <span class="badge badge-light-success fs-7 fw-bolder">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                         </div>

                        <!-- Unpaid Stat -->
                         <div class="d-flex flex-column align-items-start align-items-md-end border-start border-gray-300 ps-3 ms-1">
                             <span class="fs-8 text-muted fw-bold text-uppercase mb-1">Sisa Tagihan</span>
                             <span class="badge badge-light-danger fs-7 fw-bolder">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</span>
                         </div>
                         
                         <div class="d-none d-md-block ms-3 text-muted fs-8 fst-italic">
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
                        $status = $billDetail ? $billDetail->status : 'UNPAID';
                        $isPaid = $status == 'PAID';
                        $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
                        
                        $modalId = "bayarLainnya{$bill->id}_{$month}";
                        $showModal = $billDetail && !$isPaid && $amount > 0;
                        
                        // Define classes based on status
                        $cardClass = $isPaid ? 'paid' : ($amount > 0 ? 'unpaid' : 'bg-secondary bg-opacity-10');
                        $textColor = $isPaid ? 'text-success' : ($amount > 0 ? 'text-warning' : 'text-muted');
                    @endphp

                    @if($billDetail)
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="month-card rounded-3 p-3 h-100 d-flex flex-column justify-content-between position-relative {{ $cardClass }} {{ $showModal ? 'cursor-pointer' : '' }}"
                            @if($showModal)
                                data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"
                            @endif
                        >
                            <!-- Header: Month & Year -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold fs-7 text-gray-700">
                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                </span>
                                <span class="badge badge-light fs-9 text-muted">
                                    {{ $billDetail->year ?? ($month >= 7 ? 
                                        ($bill->academicYear->start_year ?? '-') : 
                                        ($bill->academicYear->end_year ?? '-')) 
                                    }}
                                </span>
                            </div>

                            <!-- Body: Amount -->
                            <div class="text-center my-2">
                                <span class="fw-bolder fs-6 {{ $textColor }}">
                                    @if($amount > 0)
                                        Rp {{ number_format($amount, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </span>
                                @if($isPaid && $detailPayment)
                                    <div class="fs-9 text-muted mt-2 pt-2 border-top border-gray-300">
                                        <div class="d-flex justify-content-center align-items-center mb-1">
                                            <i class="fas fa-calendar-alt me-1 fs-9"></i>
                                            {{ !empty($billDetail->paid_date) ? date('d/m/y', strtotime($billDetail->paid_date)) : '-' }}
                                        </div>
                                        <div class="fw-bold text-dark">{{ $billDetail->payment_method ?? '-' }}</div>
                                        @if(strtoupper($billDetail->payment_method) == 'TUNAI' || strtoupper($billDetail->payment_method) == 'CASH')
                                            <div class="text-primary fst-italic fs-9">
                                                <i class="fas fa-user-check me-1"></i>
                                                {{ $detailPayment->admin->name ?? $detailPayment->user->name ?? 'Admin' }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Footer: Action/Status -->
                            <div class="mt-2 d-flex justify-content-center align-items-center">
                                @if($isPaid)
                                    <span class="badge badge-light-success fw-bold px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i> Lunas
                                    </span>
                                @elseif($showModal)
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
                                            data-amount="{{ $amount }}"
                                            onclick="event.stopPropagation()">
                                        <label class="form-check-label fw-bold text-gray-700 ms-2 fs-7 cursor-pointer" for="bill-other-{{ $bill->id }}-{{ $month }}" onclick="event.stopPropagation()">
                                            Bayar
                                        </label>
                                    </div>
                                @else
                                    <span class="badge badge-light text-muted fs-8">-</span>
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
    @endforeach
</div>

<!-- Modals placed outside the grid to prevent CSS stacking context issues -->
@foreach ($billOthers as $bill)
    @foreach (array_merge(range(7, 12), range(1, 6)) as $month)
        @php
            $billDetail = $bill->bills->where('month', $month)->where('student_id', $student->id)->first();
            $amount = $billDetail ? $billDetail->amount : 0;
            $status = $billDetail ? $billDetail->status : 'UNPAID';
            $isPaid = $status == 'PAID';
            $modalId = "bayarLainnya{$bill->id}_{$month}";
            $showModal = $billDetail && !$isPaid && $amount > 0;
        @endphp

        @if($showModal)
            @include('admins.bill.table.modals.payment-modal', ['modalId' => $modalId, 'bill' => $bill, 'month' => $month,
            'student' => $student, 'amount' => $amount, 'billDetail' => $billDetail])
        @endif
    @endforeach
@endforeach