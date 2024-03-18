<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous" />
    <title>Invoice</title>
    <style>
        @font-face {
            font-family: "Plus Jakarta Sans";
            src: url("{{ asset('assets/font/PlusJakartaSans/PlusJakartaSans-Regular.ttf') }}");
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: "Plus Jakarta Sans";
            src: url("{{ asset('assets/font/PlusJakartaSans/PlusJakartaSans-Medium.ttf') }}");
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: "Plus Jakarta Sans";
            src: url("{{ asset('assets/font/PlusJakartaSans/PlusJakartaSans-Italic.ttf') }}");
            font-weight: 400;
            font-style: italic;
        }

        @font-face {
            font-family: "Plus Jakarta Sans";
            src: url("{{ asset('assets/font/PlusJakartaSans/PlusJakartaSans-SemiBold.ttf') }}");
            font-weight: 600;
            font-style: normal;
        }

        @font-face {
            font-family: "Plus Jakarta Sans";
            src: url("{{ asset('assets/font/PlusJakartaSans/PlusJakartaSans-SemiBoldItalic.ttf') }}");
            font-weight: 600;
            font-style: italic;
        }

        @font-face {
            font-family: "Plus Jakarta Sans";
            src: url("{{ asset('assets/font/PlusJakartaSans/PlusJakartaSans-Bold.ttf') }}");
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: "Plus Jakarta Sans";
            src: url("{{ asset('assets/font/PlusJakartaSans/PlusJakartaSans-ExtraBold.ttf') }}");
            font-weight: 800;
            font-style: normal;
        }

        * {
            box-sizing: border-box;
            font-family: "Plus Jakarta Sans", sans-serif !important;
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
            background-color: #fff2d6 !important;
            color: #4D0C7A;
            width: max-content;
            height: max-content;
            padding: 2px 4px;
            border-radius: 4px !important;
            font-weight: 600;
            font-size: 10px !important;
            margin-bottom: -2px !important;
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

        /* table.table-tagihan tr td {
            line-height: 28px;
        } */

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

        .text-primary {
            color: #4D0C7A !important;
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
                        <img src="{{ asset('assets/media/logos/logo-full.png') }}" width="200" alt="" />
                    </td>
                    <td align="right">
                        <h1>INVOICE</h1>
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
                                <td style="white-space: nowrap" width="15%" class="text-muted">Tanggal Pembelian</td>
                                <td width="2%">:</td>
                                <td><strong>{{ \Carbon\Carbon::parse($data->created_at)->locale('id_ID')->isoFormat('D
                                        MMMM YYYY H:mm') }}</strong></td>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap" width="15%" class="text-muted">Santri</td>
                                <td width="2%">:</td>
                                <td>
                                    <strong>{{ $data->student?->name }}</strong> <br />
                                    {{ $data->student?->classroom?->name }} <br />
                                    {{ $data->student?->classroom?->school?->name }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </header>
        <!-- main -->
        <main class="mt-4">
            <table width="100%" style="border-bottom: 2px solid rgb(232, 232, 232)" cellspacing="0">
                @if ($data->type == 'SALDO')
                <thead>
                    <th>Nama Pembayaran</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Keterangan</th>
                </thead>
                <tbody>
                    @foreach ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td class="text-primary" width="50%">
                            <span>Saldo</span>
                        </td>
                        <td align="right">Rp. {{ number_format($transaction_detail->saldoHistory->amount, 0, ',', '.')
                            }}
                        </td>
                        <td align="right">{{ $transaction_detail->saldoHistory->description ?? '' }}</td>
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
                        <td align="right">Rp. {{ number_format($transaction_detail->savingHistory->amount, 0, ',', '.')
                            }}
                        </td>
                        <td align="right">{{ $transaction_detail->savingHistory->description ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @elseif ($data->type == 'BILL')
                <thead>
                    <th>
                    <th class="text-end">Bulan</th>
                    <th class="text-end">Tahun Ajaran</th>
                    <th class="text-end">Jumlah</th>
                </thead>
                <tbody>
                    @foreach ($data->transactionDetails as $transaction_detail)
                    <tr>
                        <td class="text-primary" width="50%">
                            <span>{{ $transaction_detail->bill->billType->name ?? '' }}</span>
                        </td>
                        <td align="right">{{
                            Carbon\Carbon::parse($transaction_detail->bill->month)->translatedFormat('F') }}</td>
                        <td align="right">{{ $transaction_detail->bill->academicYear->name ?? '' }}</td>
                        <td align="right">Rp{{ number_format($transaction_detail->bill->amount, 0, ',', '.') }}</td>
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
                        Rp{{ number_format($data->transactionDetails->sum('bill.amount'), 0, ',', '.') }}
                        @elseif ($data->type == 'SAVING')
                        Rp{{ number_format($data->transactionDetails->sum('savingHistory.amount'), 0, ',', '.') }}
                        @elseif ($data->type == 'SALDO')
                        Rp{{ number_format($data->transactionDetails->sum('saldoHistory.amount'), 0, ',', '.') }}
                        @endif
                    </td>
                </tr>

                <tr>
                    <td>Biaya Transaksi</td>
                    <td align="right">Rp{{ number_format($data->xendit_fee, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Biaya Aplikasi</td>
                    <td align="right">Rp{{ number_format($data->app_fee, 0, ',', '.') }}</td>
                </tr>
                <tr class="text-strong border-table">
                    <td>TOTAL TAGIHAN</td>
                    @php $total = $data->pay_amount + $data->xendit_fee + $data->app_fee; @endphp
                    <td align="right">Rp{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
                <tr class="text-strong">
                    <td>STATUS PEMBAYARAN</td>
                    <td align="right">
                        <span class="badge-success">{{ $data->status == 'PAID' ? 'LUNAS' : 'BELUM LUNAS' }}</span>
                    </td>
                </tr>
            </table>
        </main>
        <!-- end main -->
        <!-- footer -->
        <footer>
            <table width="100%" class="mt-3" style="border-top: 1.25px solid rgb(232, 232, 232)">
                <tr style="margin-left: auto">
                    <td width="50%">
                        Metode Pembayaran: <br />
                        <strong>{{ $data->paymentMethod->name }}</strong>
                    </td>
                    @if($data->admin_id)
                    <td width="50%" align="right">
                        <strong>Petugas :</strong> <br /> {{ $data->admin->name ?? '' }}
                    </td>
                    @endif
                </tr>
            </table>
            <table width="100%" class="mt-5">
                <tr>
                    <td width="50%">
                        Invoice ini sah dan telah diproses secara otomatis oleh sistem kami.<br>
                        Jangan ragu untuk menghubungi <a href="https://cahayatasbih.or.id/" target="_blank"
                            class="text-primary text-decoration-none" style="font-weight: 600;">Puskominfo PPTQ Cahaya
                            Tasbih</a> jika Anda membutuhkan bantuan lebih lanjut.
                    </td>
                    <td width="50%" align="right" style="vertical-align: bottom">
                        <i style="font-family: 'Plus Jakarta Sans', sans-serif !important;">Terakhir diupdate:
                            {{ \Carbon\Carbon::now()->locale('id_ID')->isoFormat('D MMMM YYYY H:mm') }}</i>
                    </td>
                </tr>
            </table>
        </footer>
        <!-- end footer -->
    </div>
</body>

</html>