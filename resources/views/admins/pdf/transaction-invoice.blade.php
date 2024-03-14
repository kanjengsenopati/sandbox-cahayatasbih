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
                        <img src="{{ asset('assets/media/logos/logo-full.png') }}" width="80" alt="" />
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
                        <strong class="text-sm">KEPADA</strong>
                        <table width="100%">
                            <tr>
                                <td style="white-space: nowrap" width="15%" class="text-muted">Tanggal Pembelian</td>
                                <td width="2%">:</td>
                                <td><strong>{{ date('d F Y H:i', strtotime($data->created_at)) }}</strong></td>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap" width="15%" class="text-muted">Santri</td>
                                <td width="2%">:</td>
                                <td>
                                    <strong>{{ $data->student?->name }}</strong> <br />
                                    {{ $data->student?->classroom?->name }} <br />
                                    {{ $data->user?->classroom?->school?->name }}
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
                @if ($data->type == 'BILL')
                <thead>
                    <th>Nama Tagihan</th>
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
                    @endif
                </tbody>
            </table>
            <table width="50%" style="margin-left: auto" class="table-tagihan">
                <tr class="text-strong">
                    <td>SUB TOTAL</td>
                    <td align="right">Rp{{ number_format($data->transactionDetails->sum('bill.amount'), 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <td>Biaya Transaksi</td>
                    <td align="right">Rp{{ number_format($data->xendit_fee, 0, ',', '.') }}</td>
                </tr>
                @if ($data->discount_product > 0)
                <tr>
                    <td>Diskon Produk</td>
                    <td align="right">-Rp{{ number_format($data->discount_product, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="text-strong border-table">
                    <td>TOTAL TAGIHAN</td>
                    <td align="right">Rp{{ number_format($data->pay_amount, 0, ',', '.') }}</td>
                </tr>
                @if ($data->discount_promo > 0 || $data->discount_product > 0)
                <tr>
                    <td class="py-2">
                        <span class="badge badge-success">{{ $data->discount_promo ? 'Diskon Promo' : 'Diskon Produk'
                            }}</span>
                        <br>
                        Bebas diskon hingga Rp20.000
                    </td>
                    <td class="pt-2" align="right">
                        {{ $data->discount_promo ? 'Rp' . number_format($data->discount_promo, 0, ',', '.') : 'Rp' .
                        number_format($data->discount_product, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="badge-secondary">
                        <i>*Promo yang didapat bisa berubah. Cek
                            <a href="https://nestgymindonesia.com/" target="_blank"
                                class="text-primary text-decoration-none"
                                style="font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif !important">S&K</a>
                        </i>
                    </td>
                </tr>
                @endif
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
                </tr>
            </table>
            <table width="100%" class="mt-5">
                <tr>
                    <td width="50%" style="font-weight: 600; font-size: 14px;">
                        Invoice ini sah dan telah diproses secara otomatis oleh sistem kami.<br>
                        Jangan ragu untuk menghubungi <a href="https://cahayatasbih.or.id/" target="_blank"
                            class="text-primary text-decoration-none"
                            style="font-weight: 600; color: #4D0C7A;">Puskominfo PPTQ Cahaya
                            Tasbih</a> jika Anda membutuhkan bantuan lebih lanjut.
                    </td>
                    <td width="50%" align="right" style="vertical-align: bottom">
                        <i style="font-family: 'Plus Jakarta Sans', sans-serif !important;">Terakhir diupdate:
                            {{ date('d F Y H:i') }} WIB</i>
                    </td>
                </tr>
            </table>
        </footer>
        <!-- end footer -->
    </div>
</body>

</html>