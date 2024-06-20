@foreach ($billOthers as $bill)
<tr>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $loop->iteration }}</td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $bill->name }}<br>
        <input type="checkbox" name="bills[]" value="{{ $bill->id }}" id="bill-{{ $bill->id }}">
    </td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">Rp {{
        number_format($bill->total_unpaid,
        0, ',', '.') }}</td>
    @foreach (range(1, 12) as $month)
    @php
    $billDetail = $bill->bills->where('month',
    $month)->where('student_id', $student->id)->first();
    $amount = $billDetail ? $billDetail->amount : 0;
    $statusColor = $billDetail && $billDetail->status == 'PAID' ? '#4CAF50' : '#F44336';
    $detailPayment = $billDetail ?
    $billDetail->transactions?->first() : null;
    $modalId = "bayarKilat{$bill->id}_{$month}";
    $showModal = $billDetail && $billDetail->status !=
    'PAID';
    @endphp
    <td style="background-color: {{ $statusColor }}; padding: 10px; border: 2px solid white;">
        @if($showModal)
        <input type="checkbox" name="bill_months[{{ $bill->id }}][]" value="{{ $month }}"
            id="bill-month-{{ $bill->id }}-{{ $month }}">
        <a href="#" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" style="color: rgb(255, 255, 255);">
            Rp {{ number_format($amount, 0, ',', '.') }}
        </a>
        @include('admins.bill.table.modals.another-payment', ['modalId' => $modalId, 'bill' => $bill, 'month' => $month,
        'student' =>
        $student, 'amount' => $amount])
        @else
        <span style="color: white;">{{ $billDetail ? 'Rp ' . number_format($amount, 0, ',', '.') : 'Tidak Ada' }}</span>
        <br>
        @if($detailPayment)
        <span style="color: white;">({{
            $billDetail->paid_date ?? '' }})</span>
        <br>
        <span style="color: white;">{{
            $billDetail->payment_method ?? '' }}</span>
        @endif
        @endif
    </td>
    @endforeach
</tr>
@endforeach