@foreach ($billMonth as $bill)
<tr>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $loop->iteration }}</td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">{{ $bill->name }}</td>
    <td style="padding: 10px; border: 2px solid white;" class="text-center">Rp {{
        number_format($bill->total_unpaid,
        0, ',', '.') }}</td>
    @foreach (range(1, 12) as $month)
    @php
    $billDetail = $bill->bills->where('month',
    $month)->where('student_id', $student->id)->first();
    $amount = $billDetail ? $billDetail->amount : 0;
    $statusColor = $billDetail && $billDetail->status ==
    'PAID' ? '#B010B0' : '#FFB43A'; // Orange color
    $detailPayment = $billDetail ?
    $billDetail->transactions?->first() : null;
    $modalId = "bayarKilat{$bill->id}_{$month}";
    $showModal = $billDetail && $billDetail->status !=
    'PAID';
    @endphp
    <td style="background-color: {{ $statusColor }}; padding: 10px; border: 2px solid white;">
        @if($showModal)
        <a href="#" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" style="color: black;">
            Rp {{ number_format($amount, 0, ',', '.') }}
        </a>
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Bayar
                            Tagihan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('bill.store') }}" method="post">
                            @csrf
                            <div class="mb-3">
                                <label for="saldo" class="form-label">Saldo
                                    Siswa:</label>
                                <input type="text" class="form-control" id="saldo" name="saldo"
                                    value="Rp {{ number_format($student->saldo, 0, ',', '.') }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="jenis-tagihan" class="form-label">Jenis
                                    Tagihan:</label>
                                <input type="text" class="form-control"
                                    value="{{ $bill->name }} - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} - {{ $bill->academicYear->name }}"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Jumlah
                                    Pembayaran:</label>
                                <input type="text" class="form-control" id="amount" name="amount"
                                    value="Rp {{ number_format($amount, 0, ',', '.') }}">
                            </div>
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Metode
                                    Pembayaran:</label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="">Pilih
                                        Metode Pembayaran
                                    </option>
                                    @if ($student->saldo >
                                    $amount)
                                    <option value="BALANCE">
                                        Saldo</option>
                                    @endif
                                    <option value="CASH">
                                        Tunai</option>
                                </select>
                            </div>
                            <input type="hidden" name="bill_ids[]" value="{{ $billDetail->id }}">
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Bayar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @else
        <span style="color: white;">Rp {{
            number_format($amount, 0, ',', '.') }}</span>
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