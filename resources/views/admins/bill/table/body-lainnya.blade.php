@foreach ($billOthers as $bill)
<tr>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $loop->iteration }}</td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $bill->name ?? '' }}
    </td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">Rp {{
        number_format($bill->total_unpaid,
        0, ',', '.') }}</td>
    @foreach (range(1, 12) as $month)
    @php
    $billDetail = $bill->bills->where('month',
    $month)->where('student_id', $student->id)->first();
    $amount = $billDetail ? $billDetail->amount : 0;
    if ($billDetail) {
    if ($billDetail->status == 'UNPAID') {
    $statusColor = '#FF9800'; // Orange for UNPAID
    } elseif ($billDetail->status == 'PAID') {
    $statusColor = '#4CAF50'; // Green for PAID
    } else {
    $statusColor = '#FF9800'; // Default color (Orange) for other statuses
    }
    } else {
    $statusColor = '#BDBDBD'; // Grey for no bills
    }
    $detailPayment = $billDetail ?
    $billDetail->transactions?->first() : null;
    $modalId = "bayarKilat{$bill->id}_{$month}";
    $showModal = $billDetail && $billDetail->status !=
    'PAID';
    @endphp
    <td style="background-color: {{ $statusColor }}; padding: 10px; border: 2px solid white;">
        @if($showModal)
        <a href="#" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" style="color: rgb(255, 255, 255);">
            Rp {{ number_format($amount, 0, ',', '.') }}
        </a>
        <div style="text-align: center;">
            <input type="checkbox" name="bill_months[{{ $bill->id }}][]" value="{{ $month }}"
                id="bill-month-{{ $bill->id }}-{{ $month }}"
                class="bill-month-checkbox bill-{{ $bill->id }} prevent-modal" data-bill-id="{{ $billDetail->id }}"
                data-month="{{ $billDetail->translated_month }}" data-year="{{ $billDetail->year }}"
                data-bill-name="{{ $bill->name }}" data-amount="{{ $amount }}" style="margin-top: 10px;">
        </div>
        @include('admins.bill.table.modals.another-payment', ['modalId' => $modalId, 'bill' => $bill, 'month' => $month,
        'student' =>
        $student, 'amount' => $amount])
        @else
        <span style="color: white;">{{ $billDetail ? 'Rp ' . number_format($amount, 0, ',', '.') : 'Tidak Ada' }}</span>
        <br>
        @if($detailPayment)
        {!! !empty($billDetail->paid_date) ? '<br><span style="color: #fff;">(' . $billDetail->paid_date . ')</span>' :
        '' !!}
        <br>
        <span style="color: white;">{{
            $billDetail->payment_method ?? '' }}</span>
        @endif
        @endif
    </td>
    @endforeach
</tr>
@endforeach