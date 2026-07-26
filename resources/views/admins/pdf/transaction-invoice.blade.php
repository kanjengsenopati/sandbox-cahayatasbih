<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: sans-serif !important;
        }

        /* header */
        header table tr td {
            vertical-align: top;
            font-size: 12px !important;
        }

        header table tr td h1 {
            margin-bottom: 0;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        header table tr td p {
            font-weight: 500;
            margin-bottom: 0 !important;
        }

        header strong.text-sm {
            font-weight: 800;
            font-size: 12px !important;
        }

        /* end header */

        /* main */
        main table thead th {
            font-weight: 800;
            font-size: 12px;
            padding: 4px 0;
        }

        main table thead {
            border-top: 1.25px solid black;
            border-bottom: 1.25px solid black;
        }

        main table tbody tr td.text-primary {
            font-weight: 800;
            font-size: 12px;
            padding: 4px 0;
        }

        .text-strong td {
            font-weight: 800;
            font-size: 12px !important;
            padding: 4px 0;
        }

        .border-table {
            border-top: 1.25px dashed rgb(232, 232, 232);
            border-bottom: 1.25px dashed rgb(232, 232, 232);
        }

        .badge-success {
            margin-top: 5px !important;
            background-color: #4D0C7A !important;
            /* Warna latar belakang yang lebih gelap */
            color: #83FF3B !important;
            /* Warna teks yang cerah */
            width: max-content;
            height: max-content;
            padding: 4px 8px;
            /* Sedikit memperbesar padding */
            border-radius: 4px !important;
            font-weight: 700;
            /* Membuat font sedikit lebih tebal */
            font-size: 16px !important;
            /* Ukuran font yang sedikit lebih kecil dari 20px untuk keseimbangan */
            margin-bottom: -2px !important;
            text-transform: uppercase;
            /* Memastikan teks selalu kapital */
            letter-spacing: 0.5px;
            /* Menambah sedikit jarak antar huruf */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Menambah sedikit bayangan untuk dimensi */
        }

        .badge-danger {
            margin-top: 5px !important;
            background-color: #dc3545 !important;
            color: #ffffff !important;
            width: max-content;
            height: max-content;
            padding: 4px 8px;
            border-radius: 4px !important;
            font-weight: 700;
            font-size: 16px !important;
            text-transform: uppercase;
        }

        tr td.badge-secondary {
            background-color: #f0f2f6 !important;
            color: #6d7487 !important;
            width: 100% !important;
            font-weight: 400 !important;
            text-transform: italic;
            padding: 4px 8px;
            border-radius: 4px !important;
            overflow: hidden !important;
            text-align: center !important;
        }

        main table tbody tr td {
            font-size: 12px;
            vertical-align: center;
            padding: 4px 0;
        }

        main table tbody tr {
            padding: 8px 0;
        }

        main table tfoot {
            border-top: 1.25px solid black;
        }

        /* end main */

        /* footer */
        footer table tr td {
            font-size: 12px;
            vertical-align: top;
        }

        /* end footer */

        .text-end {
            text-align: right !important;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .text-primary {
            color: #4D0C7A !important;
        }

        .mt-3 {
            margin-top: 1rem !important;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .mt-5 {
            margin-top: 3rem !important;
        }

        .text-decoration-none {
            text-decoration: none !important;
        }
    </style>
</head>

<body>
    <div id="paper">
        <!-- header -->
        <header>
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        @php
                            $logoPath = public_path('assets/media/logos/logo-full.png');
                        @endphp
                        @if (file_exists($logoPath))
                            <img src="{{ $logoPath }}" width="200" alt="Logo" />
                        @else
                            <h2 style="color: #4D0C7A; margin: 0;">PPTQ CAHAYA TASBIH</h2>
                        @endif
                    </td>
                    <td align="right">
                        <h1>INVOICE BUKTI PEMBAYARAN</h1>
                        <p class="text-primary">{{ $data->payment_code }}</p>
                    </td>
                </tr>
            </table>
            <table width="100%" cellspacing="0" cellpadding="0" class="mt-4">
                <tr>
                    <td width="50%">
                        <strong class="text-sm">Informasi Transaksi</strong>
                        <table width="100%">
                            <tr>
                                <td style="white-space: nowrap" width="15%" class="text-muted">Tanggal</td>
                                <td width="2%">:</td>
                                <td><strong>{{ \Carbon\Carbon::parse($data->created_at)->locale('id_ID')->isoFormat('D MMMM YYYY H:mm') }}</strong></td>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap" width="15%" class="text-muted">Nama Siswa/Santri</td>
                                <td width="2%">:</td>
                                <td>
                                    <strong>{{ $data->student?->name }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap" width="15%" class="text-muted">Kelas</td>
                                <td width="2%">:</td>
                                <td>
                                    {{ $data->student?->classroom?->name ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap" width="15%" class="text-muted">Lembaga</td>
                                <td width="2%">:</td>
                                <td>
                                    {{ $data->student?->classroom?->school?->name ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </header>
        <!-- main -->
        <main class="mt-4">
            <table width="100%" style="border-bottom: 2px solid rgb(65, 59, 59)" cellspacing="0">
                @if ($data->type == 'SALDO')
                <thead>
                    <th>Nama Pembayaran</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Keterangan</th>
                </thead>
                <tbody>
                    @foreach ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td class="text-primary" width="40%">
                            <span>Saldo</span>
                        </td>
                        <td align="right">Rp. {{ number_format($transaction_detail->saldoHistory?->amount ?? 0, 0, ',', '.') }}
                        </td>
                        <td align="right">{{ $transaction_detail->saldoHistory?->description ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @elseif ($data->type == 'SAVING')
                <thead>
                    <th>Nama Pembayaran</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Keterangan</th>
                </thead>
                <tbody>
                    @foreach ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td class="text-primary" width="50%">
                            <span>Tabungan</span>
                        </td>
                        <td align="right">Rp. {{ number_format($transaction_detail->savingHistory?->amount ?? 0, 0, ',', '.') }}
                        </td>
                        <td align="right">{{ $transaction_detail->savingHistory?->description ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @elseif ($data->type == 'BILL')
                <thead>
                    <th>Item Tagihan</th>
                    <th class="text-end">Bulan</th>
                    <th class="text-end">Tahun</th>
                    <th class="text-end">Nominal</th>
                </thead>
                <tbody>
                    @foreach ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td class="text-primary" width="40%">
                            <span>{{ $transaction_detail?->bill?->billType?->name ?? '' }}</span>
                        </td>
                        <td align="right">{{ $transaction_detail?->bill?->translated_month ?? '' }}</td>
                        <td align="right">{{ $transaction_detail?->bill?->year ?? '' }}</td>
                        <td align="right">Rp{{ number_format($transaction_detail?->bill?->amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @endif
            </table>
            <table width="50%" style="margin-left: auto" class="table-tagihan">
                <tr class="text-strong">
                    <td>SUB TOTAL</td>
                    <td align="right">
                        @if ($data->type == 'BILL')
                        Rp{{ number_format($data->transactionDetails->sum(function($d) { return $d->bill?->amount ?? 0; }), 0, ',', '.') }}
                        @elseif ($data->type == 'SAVING')
                        Rp{{ number_format($data->transactionDetails->sum(function($d) { return $d->savingHistory?->amount ?? 0; }), 0, ',', '.') }}
                        @elseif ($data->type == 'SALDO')
                        Rp{{ number_format($data->transactionDetails->sum(function($d) { return $d->saldoHistory?->amount ?? 0; }), 0, ',', '.') }}
                        @else
                        Rp{{ number_format($data->pay_amount ?? 0, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>

                @if ($data->xendit_fee > 0)
                <tr>
                    <td>Biaya Transaksi</td>
                    <td align="right">Rp{{ number_format($data->xendit_fee, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if ($data->app_fee > 0)
                <tr>
                    <td>Biaya Aplikasi</td>
                    <td align="right">
                        Rp{{ number_format($data->app_fee, 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                @if ($data->unique_payment > 0)
                <tr>
                    <td>Kode Unik Transaksi</td>
                    <td align="right">
                        Rp{{ number_format($data->unique_payment, 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                <tr class="text-strong border-table">
                    <td>TOTAL TAGIHAN</td>
                    @php
                    $total = $data->pay_amount ?? 0;
                    if ($data->xendit_fee > 0) {
                        $total += $data->xendit_fee + ($data->app_fee ?? 0);
                    }
                    @endphp
                    <td align="right">Rp{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
                <tr class="text-strong">
                    <td>STATUS PEMBAYARAN</td>
                    <td align="right">
                        @if($data->status == 'PAID')
                        <span class="badge badge-success">LUNAS</span>
                        @else
                        <span class="badge badge-danger">BELUM LUNAS</span>
                        @endif
                    </td>
                </tr>
            </table>
        </main>
        <!-- end main -->
        <!-- footer -->
        <footer>
            <table width="100%" class="mt-3" style="border-top: 1.25px solid rgb(232, 232, 232)">
                <tr>
                    <td width="50%">
                        Metode Pembayaran: <br />
                        <strong>{{ $data?->paymentMethod?->name ?? '' }}</strong>
                    </td>
                    @if($data->admin_id)
                    <td width="50%" align="right">
                        <strong>Nama Petugas :</strong> <br /> {{ $data?->admin?->name ?? 'CT-PAY' }}
                    </td>
                    @endif
                </tr>
            </table>
            <table width="100%" class="mt-5">
                <tr>
                    <td width="60%" style="vertical-align: top;">
                        Invoice bukti pembayaran ini sah dan telah diproses secara otomatis oleh sistem kami.<br>
                        Jangan ragu untuk menghubungi <a href="https://cahayatasbih.or.id/" target="_blank"
                            class="text-primary text-decoration-none" style="font-weight: 600;">PUSKOMINFO PPTQ CAHAYA
                            TASBIH</a> jika Anda membutuhkan bantuan lebih lanjut.
                    </td>
                    <td width="40%" align="right" style="vertical-align: top;">
                        <img src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(100)->generate(route('transaction.invoice', $data->id))) !!}"
                            alt="qrcode" style="display: block; margin-left: auto; margin-bottom: 10px; width: 100px; height: 100px;" />
                    </td>
                </tr>
            </table>
            <table width="100%" style="margin-top: 10px;">
                <tr>
                    <td align="right">
                        <i style="font-style: italic;">Terakhir diupdate:
                            {{ \Carbon\Carbon::now()->locale('id_ID')->isoFormat('D MMMM YYYY H:mm') }}</i>
                    </td>
                </tr>
            </table>
        </footer>
        <!-- end footer -->
    </div>
</body>

</html>