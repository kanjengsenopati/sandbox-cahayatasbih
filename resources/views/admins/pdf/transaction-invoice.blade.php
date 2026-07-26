<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice Bukti Pembayaran</title>
    <style>
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            src: url("{{ public_path('assets/font/inter_normal_fc629c4846da87baba81574557da9dc5.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 600;
            src: url("{{ public_path('assets/font/inter_600_c6343ce426df2848a2aa7b4752ed9ad5.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 700;
            src: url("{{ public_path('assets/font/inter_bold_7f75c62596803334c7d5a010f1654ff0.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 800;
            src: url("{{ public_path('assets/font/inter_800_b23e7a58bc1666987233ff4f273bb149.ttf') }}") format('truetype');
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', Helvetica, Arial, sans-serif !important;
            margin: 0;
            padding: 0;
        }

        body {
            color: #1E293B;
            font-size: 12px;
            line-height: 1.5;
            background-color: #FFFFFF;
            padding: 24px;
        }

        #paper {
            width: 100%;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 800;
            color: #4D0C7A;
            letter-spacing: 0.5px;
        }

        .invoice-title {
            font-size: 15px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .invoice-code {
            font-size: 13px;
            font-weight: 700;
            color: #4D0C7A;
            margin-top: 2px;
        }

        /* Information Box */
        .info-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
        }

        .info-card-header {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748B;
            margin-bottom: 8px;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 4px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            font-size: 12px;
            vertical-align: top;
        }

        .label-cell {
            color: #64748B;
            font-weight: 500;
            width: 140px;
        }

        .colon-cell {
            color: #64748B;
            width: 15px;
        }

        .value-cell {
            color: #0F172A;
            font-weight: 600;
        }

        /* Modern Data Table */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table thead tr {
            background-color: #F1F5F9;
        }

        .data-table thead th {
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 14px;
            border-bottom: 1.5px solid #CBD5E1;
            text-align: left;
        }

        .data-table thead th.text-end {
            text-align: right;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #F1F5F9;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody td {
            padding: 11px 14px;
            font-size: 12px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
        }

        .data-table tbody td.text-end {
            text-align: right;
        }

        .item-name {
            font-weight: 700;
            color: #4D0C7A;
        }

        .item-desc {
            color: #64748B;
            font-size: 11px;
        }

        /* Summary & Totals Table */
        .summary-wrapper {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 24px;
        }

        .summary-table {
            width: 48%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 5px 8px;
            font-size: 12px;
        }

        .summary-label {
            color: #475569;
            font-weight: 600;
        }

        .summary-value {
            text-align: right;
            color: #0F172A;
            font-weight: 600;
        }

        /* Total Highlight Container */
        .total-box {
            background-color: #F3E8FF;
            border: 1px solid #E9D5FF;
            border-radius: 6px;
        }

        .total-box td {
            padding: 8px 12px !important;
        }

        .total-label {
            color: #4D0C7A !important;
            font-weight: 800 !important;
            font-size: 13px !important;
        }

        .total-value {
            color: #4D0C7A !important;
            font-weight: 800 !important;
            font-size: 14px !important;
            text-align: right;
        }

        /* Badge Status */
        .badge-pill-success {
            background-color: #DEF7EC;
            color: #03543F;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .badge-pill-danger {
            background-color: #FDE8E8;
            color: #9B1C1C;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        /* Footer */
        .footer-divider {
            border-top: 1px solid #E2E8F0;
            margin-top: 20px;
            padding-top: 16px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: top;
            font-size: 11px;
            color: #64748B;
        }

        .footer-note {
            line-height: 1.6;
        }

        .footer-note a {
            color: #4D0C7A;
            text-decoration: none;
            font-weight: 600;
        }

        .timestamp {
            margin-top: 12px;
            text-align: right;
            font-size: 10px;
            color: #94A3B8;
            font-style: italic;
        }

        .text-end {
            text-align: right !important;
        }
    </style>
</head>

<body>
    <div id="paper">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="50%">
                    @php
                        $logoPath = public_path('assets/media/logos/logo-full.png');
                    @endphp
                    @if (file_exists($logoPath))
                        <img src="{{ $logoPath }}" width="180" alt="Logo PPTQ Cahaya Tasbih" />
                    @else
                        <div class="brand-title">PPTQ CAHAYA TASBIH</div>
                    @endif
                </td>
                <td width="50%" class="text-end">
                    <div class="invoice-title">INVOICE BUKTI PEMBAYARAN</div>
                    <div class="invoice-code">{{ $data->payment_code }}</div>
                </td>
            </tr>
        </table>

        <!-- Transaction Information Card -->
        <div class="info-card">
            <div class="info-card-header">Informasi Transaksi</div>
            <table class="info-table">
                <tr>
                    <td class="label-cell">Tanggal Transaksi</td>
                    <td class="colon-cell">:</td>
                    <td class="value-cell">{{ \Carbon\Carbon::parse($data->created_at)->locale('id_ID')->isoFormat('D MMMM YYYY HH:mm') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Nama Siswa / Santri</td>
                    <td class="colon-cell">:</td>
                    <td class="value-cell">{{ $data->student?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Kelas</td>
                    <td class="colon-cell">:</td>
                    <td class="value-cell">{{ $data->student?->classroom?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Lembaga</td>
                    <td class="colon-cell">:</td>
                    <td class="value-cell">{{ $data->student?->classroom?->school?->name ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Modern Data Table -->
        <main>
            <table class="data-table">
                @if ($data->type == 'SALDO')
                <thead>
                    <tr>
                        <th width="50%">Nama Pembayaran</th>
                        <th class="text-end" width="25%">Jumlah</th>
                        <th class="text-end" width="25%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td>
                            <span class="item-name">Top Up Saldo</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($transaction_detail->saldoHistory?->amount ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end item-desc">{{ $transaction_detail->saldoHistory?->description ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada detail transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
                @elseif ($data->type == 'SAVING')
                <thead>
                    <tr>
                        <th width="50%">Nama Pembayaran</th>
                        <th class="text-end" width="25%">Jumlah</th>
                        <th class="text-end" width="25%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td>
                            <span class="item-name">Setoran Tabungan</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($transaction_detail->savingHistory?->amount ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end item-desc">{{ $transaction_detail->savingHistory?->description ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada detail transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
                @else
                <thead>
                    <tr>
                        <th width="45%">Item Tagihan</th>
                        <th class="text-end" width="18%">Bulan</th>
                        <th class="text-end" width="15%">Tahun</th>
                        <th class="text-end" width="22%">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td>
                            <span class="item-name">{{ $transaction_detail?->bill?->billType?->name ?? 'Pembayaran Tagihan' }}</span>
                        </td>
                        <td class="text-end">{{ $transaction_detail?->bill?->translated_month ?? '-' }}</td>
                        <td class="text-end">{{ $transaction_detail?->bill?->year ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($transaction_detail?->bill?->amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-end">Rp {{ number_format($data->pay_amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
                @endif
            </table>

            <!-- Summary & Totals -->
            <div class="summary-wrapper">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">SUB TOTAL</td>
                        <td class="summary-value">
                            @if ($data->type == 'BILL')
                            Rp {{ number_format($data->transactionDetails->sum(function($d) { return $d->bill?->amount ?? 0; }), 0, ',', '.') }}
                            @elseif ($data->type == 'SAVING')
                            Rp {{ number_format($data->transactionDetails->sum(function($d) { return $d->savingHistory?->amount ?? 0; }), 0, ',', '.') }}
                            @elseif ($data->type == 'SALDO')
                            Rp {{ number_format($data->transactionDetails->sum(function($d) { return $d->saldoHistory?->amount ?? 0; }), 0, ',', '.') }}
                            @else
                            Rp {{ number_format($data->pay_amount ?? 0, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>

                    @if (($data->xendit_fee ?? 0) > 0)
                    <tr>
                        <td class="summary-label">Biaya Transaksi</td>
                        <td class="summary-value">Rp {{ number_format($data->xendit_fee, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if (($data->app_fee ?? 0) > 0)
                    <tr>
                        <td class="summary-label">Biaya Aplikasi</td>
                        <td class="summary-value">Rp {{ number_format($data->app_fee, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if (($data->unique_payment ?? 0) > 0)
                    <tr>
                        <td class="summary-label">Kode Unik Transaksi</td>
                        <td class="summary-value">Rp {{ number_format($data->unique_payment, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    <tr class="total-box">
                        <td class="total-label">TOTAL TAGIHAN</td>
                        @php
                            $total = $data->pay_amount ?? 0;
                            if (($data->xendit_fee ?? 0) > 0) {
                                $total += $data->xendit_fee + ($data->app_fee ?? 0);
                            }
                        @endphp
                        <td class="total-value">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td class="summary-label" style="padding-top: 8px;">STATUS PEMBAYARAN</td>
                        <td class="summary-value" style="padding-top: 8px;">
                            @if($data->status == 'PAID')
                            <span class="badge-pill-success">LUNAS</span>
                            @else
                            <span class="badge-pill-danger">BELUM LUNAS</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer-divider">
            <table class="footer-table">
                <tr>
                    <td width="50%">
                        Metode Pembayaran: <br />
                        <strong style="color: #0F172A; font-size: 12px;">{{ $data?->paymentMethod?->name ?? 'Tunai' }}</strong>
                    </td>
                    @if($data->admin_id)
                    <td width="50%" class="text-end">
                        Nama Petugas: <br />
                        <strong style="color: #0F172A; font-size: 12px;">{{ $data?->admin?->name ?? 'CT-PAY' }}</strong>
                    </td>
                    @endif
                </tr>
            </table>

            <table class="footer-table" style="margin-top: 16px;">
                <tr>
                    <td width="65%" class="footer-note">
                        Invoice bukti pembayaran ini sah dan telah diproses secara otomatis oleh sistem kami.<br />
                        Jika Anda membutuhkan bantuan lebih lanjut, silakan hubungi
                        <a href="https://cahayatasbih.or.id/" target="_blank">PUSKOMINFO PPTQ CAHAYA TASBIH</a>.
                    </td>
                    <td width="35%" class="text-end">
                        <img src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(90)->generate(route('transaction.invoice', $data->id))) !!}"
                            alt="QR Verification" style="display: inline-block; width: 85px; height: 85px;" />
                    </td>
                </tr>
            </table>

            <div class="timestamp">
                Cetak Terakhir: {{ \Carbon\Carbon::now()->locale('id_ID')->isoFormat('D MMMM YYYY HH:mm') }} WIB
            </div>
        </footer>
    </div>
</body>

</html>