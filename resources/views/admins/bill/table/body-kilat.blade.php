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
    .form-check-custom .form-check-input {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
@endpush

<div class="accordion" id="accordionKilatParent">
    @foreach ($billMonth as $bill)
    @php
        $paidAmount = $bill->bills->where('student_id', $student->id)->where('status', 'PAID')->sum('amount');
        $unpaidAmount = $bill->total_unpaid;
    @endphp
    
    <div class="accordion-item mb-5 border border-gray-300 shadow-sm rounded-3 overflow-hidden">
        <h2 class="accordion-header" id="headingKilat{{ $bill->id }}">
            <button class="accordion-button fs-4 fw-bold collapsed bg-light text-dark d-block" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseKilat{{ $bill->id }}" 
                aria-expanded="false" 
                aria-controls="collapseKilat{{ $bill->id }}">
                
                <div class="row w-100 align-items-center pe-3">
                    <!-- Left: Title & Year -->
                    <div class="col-md-6 d-flex flex-column text-start">
                         <div class="d-flex align-items-center mb-1">
                             <span class="text-slate-900 fs-5 fw-bolder me-2">{{ $bill->name }}</span>
                             <span class="badge badge-primary fw-bold fs-8 px-3 py-1">Bulanan</span>
                         </div>
                         <span class="text-slate-500 fs-7 fw-bold">
                            <i class="fas fa-calendar-alt me-1 text-slate-400 fs-8"></i>
                            Tahun Ajaran {{ $bill->academicYear->name ?? '-' }}
                         </span>
                    </div>

                    <!-- Right: Stats & Action -->
                    <div class="col-md-6 d-flex justify-content-md-end align-items-center mt-3 mt-md-0 gap-2 gap-md-4">
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
                         
                         <div class="d-none d-md-block ms-3 text-slate-400 fs-8 fw-bold">
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
                        $billDetail = $bill->bills->where('month', $month)->where('student_id', $student->id)->first();
                        $amount = $billDetail ? $billDetail->amount : 0;
                        $status = $billDetail ? $billDetail->status : 'UNPAID';
                        $isPaid = $status == 'PAID';
                        $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
                        
                        $modalId = "bayarKilat{$bill->id}_{$month}";
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
                                <span class="fw-bold fs-7 text-slate-800">
                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                </span>
                                <span class="badge badge-secondary fs-9 text-slate-600 fw-bold">
                                    {{ $billDetail->year ?? ($month >= 7 ? 
                                        ($bill->academicYear->start_year ?? '-') : 
                                        ($bill->academicYear->end_year ?? '-')) 
                                    }}
                                </span>
                            </div>

                            <!-- Body: Amount -->
                            <div class="text-center my-2">
                                <span class="fw-bolder fs-5 {{ $isPaid ? 'text-emerald-600' : ($amount > 0 ? 'text-amber-600' : 'text-slate-400') }}">
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </span>
                                @if($isPaid && $detailPayment)
                                    <div class="fs-9 text-slate-500 mt-2 pt-2 border-top border-gray-200">
                                        <div class="d-flex justify-content-center align-items-center mb-1 fw-bold">
                                            <i class="fas fa-calendar-alt me-1 fs-9"></i>
                                            {{ !empty($billDetail->paid_date) ? date('d/m/y', strtotime($billDetail->paid_date)) : '-' }}
                                        </div>
                                        <div class="fw-bolder text-slate-700">{{ $billDetail->payment_method ?? '-' }}</div>
                                        @if(strtoupper($billDetail->payment_method) == 'TUNAI' || strtoupper($billDetail->payment_method) == 'CASH')
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
                                @if($isPaid)
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
                                            data-bill-id="{{ $billDetail->id }}"
                                            data-month="{{ $billDetail->translated_month }}" 
                                            data-year="{{ $billDetail->year }}"
                                            data-bill-name="{{ $bill->name }}" 
                                            data-amount="{{ $amount }}"
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
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Modals placed outside the grid to prevent CSS stacking context issues -->
@foreach ($billMonth as $bill)
    @foreach (array_merge(range(7, 12), range(1, 6)) as $month)
        @php
            $billDetail = $bill->bills->where('month', $month)->where('student_id', $student->id)->first();
            $amount = $billDetail ? $billDetail->amount : 0;
            $status = $billDetail ? $billDetail->status : 'UNPAID';
            $isPaid = $status == 'PAID';
            $modalId = "bayarKilat{$bill->id}_{$month}";
            $showModal = $billDetail && !$isPaid && $amount > 0;
        @endphp

        @if($showModal)
            @include('admins.bill.table.modals.payment-modal', ['modalId' => $modalId, 'bill' => $bill, 'month' => $month,
            'student' => $student, 'amount' => $amount, 'billDetail' => $billDetail])
        @endif
    @endforeach
@endforeach
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');

        selectAllCheckbox.addEventListener('change', function() {
        const billMonthCheckboxes = document.querySelectorAll('.bill-month-checkbox');
        billMonthCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        });
        });

        // Prevent the modal from opening when clicking on checkboxes
        const preventModalCheckboxes = document.querySelectorAll('.prevent-modal');
        preventModalCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(event) {
        event.stopPropagation();
        });
        });

        // Handle "Bayar" button click
        document.querySelector('.modal-pay').addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.bill-month-checkbox:checked');
        const paymentDetails = document.getElementById('payment-details');
        const totalAmountElement = document.getElementById('total-amount');

        paymentDetails.innerHTML = ''; // Clear previous details

        let totalAmount = 0;

        // Remove any existing bill_ids hidden inputs
        document.querySelectorAll('input[name="bill_ids[]"]').forEach(input => input.remove());

        selectedCheckboxes.forEach(checkbox => {
        const billId = checkbox.getAttribute('data-bill-id');
        const billName = checkbox.getAttribute('data-bill-name');
        const translatedMonth = checkbox.getAttribute('data-month');
        const year = checkbox.getAttribute('data-year');
        const month = checkbox.value;
        const amount = parseInt(checkbox.getAttribute('data-amount'));


        if (!isNaN(amount)) {
        totalAmount += amount;

        // check jika student->saldo < totalAmount maka hidden option payment method value='BALANCE'
        const studentBalance = parseInt('{{ $student->saldo }}');
        const paymentMethod = document.getElementById('payment-method');
        if (studentBalance < totalAmount) {
        // sembunyikan option payment method value='BALANCE'
        paymentMethod.querySelector('option[value="BALANCE"]').style.display = 'none';
        } else {
        // tampilkan option payment method value='BALANCE'
        paymentMethod.querySelector('option[value="BALANCE"]').style.display = 'block';
        }


        // Create a new hidden input for each selected bill ID
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'bill_ids[]';
        hiddenInput.value = billId;
        // append hidden input to form with id form-multi-payment
        document.getElementById('form-multi-payment').appendChild(hiddenInput);



        // Create a new card element for each selected bill
        const cardDiv = document.createElement('div');
        cardDiv.className = 'card payment-card'; // Add class for styling

        const cardBodyDiv = document.createElement('div');
        cardBodyDiv.className = 'card-body pt-5';

        const nameDiv = document.createElement('div');
        nameDiv.className = 'mb-1';
        const nameSpan = document.createElement('span');
        nameSpan.className = 'fw-bold fs-5';
        nameSpan.textContent = `${billName}, ${translatedMonth} ${year}`;
        nameDiv.appendChild(nameSpan);

        const amountDiv = document.createElement('div');
        amountDiv.className = 'mb-1';
        const amountSpan = document.createElement('span');
        amountSpan.className = 'fw-bold text-muted';
        amountSpan.textContent = `Rp ${amount.toLocaleString()}`;
        amountDiv.appendChild(amountSpan);

        cardBodyDiv.appendChild(nameDiv);
        cardBodyDiv.appendChild(amountDiv);
        cardDiv.appendChild(cardBodyDiv);

        paymentDetails.appendChild(cardDiv);
        }
        });

        // Update total amount
        totalAmountElement.textContent = `Rp ${totalAmount.toLocaleString()}`;
        });

        });
</script>
@endpush