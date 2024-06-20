@foreach ($billMonth as $bill)
<tr>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $loop->iteration }}</td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $bill->name }}
    </td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">Rp {{ number_format($bill->total_unpaid, 0,
        ',', '.') }}</td>
    @foreach (range(1, 12) as $month)
    @php
    $billDetail = $bill->bills->where('month', $month)->where('student_id', $student->id)->first();
    $amount = $billDetail ? $billDetail->amount : 0;
    $statusColor = $billDetail && $billDetail->status == 'PAID' ? '#4CAF50' : '#F44336';
    $detailPayment = $billDetail ? $billDetail->transactions?->first() : null;
    $modalId = "bayarKilat{$bill->id}_{$month}";
    $showModal = $billDetail && $billDetail->status != 'PAID';
    @endphp
    <td style="background-color: {{ $statusColor }}; padding: 10px; border: 2px solid white;">
        @if($showModal)

        <a href="#" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" style="color: #fff;">
            Rp {{ number_format($amount, 0, ',', '.') }}<br>
            <input type="checkbox" name="bill_months[{{ $bill->id }}][]" value="{{ $month }}"
                id="bill-month-{{ $bill->id }}-{{ $month }}" class="bill-month-checkbox bill-{{ $bill->id }}">
        </a>
        @include('admins.bill.table.modals.payment-modal', ['modalId' => $modalId, 'bill' => $bill, 'month' => $month,
        'student' =>
        $student, 'amount' => $amount, 'billDetail' => $billDetail])
        @else
        <span style="color: #fff;">Rp {{ number_format($amount, 0, ',', '.') }}</span>
        @if($detailPayment)
        <br><span style="color: #fff;">({{ $billDetail->paid_date ?? '' }})</span>
        <br><span style="color: #fff;">{{ $billDetail->payment_method ?? '' }}</span>
        @endif
        @endif
    </td>
    @endforeach
</tr>
@endforeach