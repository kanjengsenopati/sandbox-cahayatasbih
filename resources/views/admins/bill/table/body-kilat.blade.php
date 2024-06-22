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
@foreach ($billMonth as $bill)
<tr>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $loop->iteration }}</td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $bill->name }}</td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">Rp {{ number_format($bill->total_unpaid, 0,
        ',', '.') }}</td>
    @foreach (range(1, 12) as $month)
    @php
    $billDetail = $bill->bills->where('month', $month)->where('student_id', $student->id)->first();
    $amount = $billDetail ? $billDetail->amount : 0;
    $statusColor = $billDetail && $billDetail->status == 'PAID' ? '#4CAF50' : '#FF9800';
    $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
    $modalId = "bayarKilat{$bill->id}_{$month}";
    $showModal = $billDetail && $billDetail->status != 'PAID';
    @endphp
    <td style="background-color: {{ $statusColor }}; padding: 10px; border: 2px solid white; position: relative;">
        @if($showModal)
        <div style="text-align: center;">
            <a href="#" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" style="color: #fff; display: block;">
                Rp {{ number_format($amount, 0, ',', '.') }}
            </a>
            <input type="checkbox" name="bill_months[{{ $bill->id }}][]" value="{{ $month }}"
                id="bill-month-{{ $bill->id }}-{{ $month }}"
                class="bill-month-checkbox bill-{{ $bill->id }} prevent-modal" data-bill-id="{{ $bill->id }}"
                data-month="{{ $billDetail->translated_month }}" data-year="{{ $billDetail->year }}"
                data-bill-name="{{ $bill->name }}" data-amount="{{ $amount }}" style="margin-top: 10px;">
        </div>
        @include('admins.bill.table.modals.payment-modal', ['modalId' => $modalId, 'bill' => $bill, 'month' => $month,
        'student' => $student, 'amount' => $amount, 'billDetail' => $billDetail])
        @else
        <span style="color: #fff; display: block;">Rp {{ number_format($amount, 0, ',', '.') }}</span>
        @if($detailPayment)
        <br><span style="color: #fff;">({{ $billDetail->paid_date ?? '' }})</span>
        <br><span style="color: #fff;">{{ $billDetail->payment_method ?? '' }}</span>
        @endif
        @endif
    </td>
    @endforeach
</tr>
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

            selectedCheckboxes.forEach(checkbox => {
                const billId = checkbox.getAttribute('data-bill-id');
                const billName = checkbox.getAttribute('data-bill-name');
                const translatedMonth = checkbox.getAttribute('data-month');
                const year = checkbox.getAttribute('data-year');
                const month = checkbox.value;
                const amount = parseInt(checkbox.getAttribute('data-amount'));

                if (!isNaN(amount)) {
                    totalAmount += amount;
                    
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