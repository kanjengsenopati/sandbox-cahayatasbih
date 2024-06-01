<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Bayar Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('bill.store') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="saldo" class="form-label">Saldo Siswa:</label>
                        <input type="text" class="form-control" id="saldo" name="saldo"
                            value="Rp {{ number_format($student->saldo, 0, ',', '.') }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="jenis-tagihan" class="form-label">Jenis Tagihan:</label>
                        <input type="text" class="form-control"
                            value="{{ $bill->name }} - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} - {{ $bill->academicYear->name }}"
                            readonly>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah Pembayaran:</label>
                        <input type="text" class="form-control" id="amount" name="amount"
                            value="Rp {{ number_format($amount, 0, ',', '.') }}">
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran:</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="">Pilih Metode Pembayaran</option>
                            @if ($student->saldo > $amount)
                            <option value="BALANCE">Saldo</option>
                            @endif
                            <option value="CASH">Tunai</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Bayar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>