<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice Bukti Pembayaran</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0mm;
        }

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

        html, body {
            width: 210mm;
            color: #1E293B;
            font-size: 10px;
            line-height: 1.3;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        .a4-sheet {
            width: 210mm;
            position: relative;
        }

        .page-break {
            page-break-after: always;
        }

        /* 1/3 A4 Slip Container: 210mm x 98.5mm (3 slips fit strictly inside 297mm A4) */
        .receipt-slip {
            width: 210mm;
            height: 98.5mm;
            max-height: 98.5mm;
            padding: 4.5mm 9mm 3.5mm 9mm;
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
        }

        .receipt-slip.with-cutline {
            border-bottom: 1.2px dashed #94A3B8;
        }

        .cut-indicator {
            position: absolute;
            bottom: 1px;
            right: 9mm;
            font-size: 7.5px;
            color: #64748B;
            font-weight: 600;
            background-color: #F8FAFC;
            padding: 0px 5px;
            border-radius: 3px;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #E2E8F0;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-title {
            font-size: 13px;
            font-weight: 800;
            color: #4D0C7A;
            letter-spacing: 0.3px;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 8.5px;
            color: #64748B;
            font-weight: 600;
            margin-top: 1px;
        }

        .invoice-title {
            font-size: 11px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            display: inline-block;
            vertical-align: middle;
        }

        .invoice-code {
            font-size: 10px;
            font-weight: 700;
            color: #4D0C7A;
            margin-top: 1px;
        }

        /* Badge Status */
        .badge-pill-success {
            background-color: #DEF7EC;
            color: #03543F;
            padding: 1.5px 7px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 8.5px;
            letter-spacing: 0.3px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 4px;
        }

        .badge-pill-danger {
            background-color: #FDE8E8;
            color: #9B1C1C;
            padding: 1.5px 7px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 8.5px;
            letter-spacing: 0.3px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 4px;
        }

        /* Student & Transaction Info Compact Box */
        .info-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 5px 9px;
            margin-bottom: 6px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 1px 0;
            font-size: 9.5px;
            vertical-align: top;
            line-height: 1.25;
        }

        .label-cell {
            color: #475569;
            font-weight: 600;
            width: 78px;
        }

        .colon-cell {
            color: #475569;
            width: 8px;
            text-align: center;
        }

        .value-cell {
            color: #0F172A;
            font-weight: 700;
        }

        /* Main 2-Column Layout Table */
        .main-layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Data Table (Left Column) */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            overflow: hidden;
        }

        .data-table thead tr {
            background-color: #F1F5F9;
        }

        .data-table thead th {
            color: #334155;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 4.5px 7px;
            border-bottom: 1px solid #CBD5E1;
            text-align: left;
        }

        .data-table tbody td {
            padding: 4.5px 7px;
            font-size: 9.5px;
            color: #1E293B;
            vertical-align: top;
            border-bottom: 1px solid #F1F5F9;
            line-height: 1.25;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Auto-Compact Mode when >= 4 BillType groups */
        .data-table.compact-mode thead th {
            padding: 3px 5px;
            font-size: 8px;
        }

        .data-table.compact-mode tbody td {
            padding: 2.5px 5px;
            font-size: 8.5px;
            line-height: 1.15;
        }

        .item-name {
            font-weight: 700;
            color: #4D0C7A;
        }

        .item-count-badge {
            font-size: 8px;
            color: #475569;
            font-weight: 600;
            display: inline-block;
            margin-left: 2px;
        }

        .period-year-row {
            margin-bottom: 1.5px;
            line-height: 1.2;
        }

        .period-year-row:last-child {
            margin-bottom: 0;
        }

        .period-year-badge {
            background-color: #E2E8F0;
            color: #1E293B;
            font-weight: 700;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 3px;
            display: inline-block;
            margin-right: 3px;
        }

        .period-months-text {
            font-size: 8.5px;
            color: #334155;
            font-weight: 600;
        }

        .item-desc {
            color: #475569;
            font-size: 8.5px;
        }

        /* Right Summary & Verification Panel */
        .summary-panel {
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            padding: 6px 8px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 2px 2px;
            font-size: 9px;
        }

        .summary-label {
            color: #475569;
            font-weight: 600;
        }

        .summary-value {
            text-align: right;
            color: #0F172A;
            font-weight: 700;
        }

        .total-box {
            background-color: #ECFDF5;
            border: 1px solid #A7F3D0;
            border-radius: 5px;
            margin-top: 3px;
            padding: 5px 7px;
        }

        .total-box-table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-label {
            color: #065F46;
            font-weight: 800;
            font-size: 9.5px;
            text-transform: uppercase;
        }

        .total-value {
            color: #059669;
            font-weight: 800;
            font-size: 11px;
            text-align: right;
        }

        .verification-divider {
            border-top: 1px solid #E2E8F0;
            margin-top: 6px;
            padding-top: 5px;
        }

        .verification-note {
            font-size: 7.5px;
            color: #475569;
            line-height: 1.3;
        }

        .verification-note strong {
            color: #0F172A;
            font-weight: 700;
        }

        .verification-note a {
            color: #4D0C7A;
            text-decoration: none;
            font-weight: 700;
        }

        .text-end {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }
    </style>
</head>

<body>
    @php
        $shortMonthsMap = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $formatSmartMonths = function ($yearItems, bool $useCompact) use ($shortMonthsMap) {
            $sortedItems = $yearItems->sortBy(fn($d) => (int) ($d->bill?->month ?? 0))->values();
            $monthNums = $sortedItems
                ->map(fn($d) => (int) ($d->bill?->month ?? 0))
                ->filter(fn($m) => $m >= 1 && $m <= 12)
                ->unique()
                ->values()
                ->all();

            if (empty($monthNums)) {
                $fallback = $sortedItems->map(fn($d) => $d->bill?->translated_month ?? '')->filter()->implode(', ');
                return $fallback !== '' ? $fallback : 'Sekali Bayar';
            }

            // Jika bulan sedikit (<= 3) dan bukan mode compact, tampilkan nama bulan lengkap
            if (!$useCompact && count($monthNums) <= 3) {
                return $sortedItems->map(fn($d) => $d->bill?->translated_month ?? ($shortMonthsMap[(int)$d->bill?->month] ?? ''))->filter()->unique()->implode(', ');
            }

            // Kelompokkan bulan berurutan menjadi rentang (contoh: Jul-Des, Jan-Mar)
            $ranges = [];
            $start = $monthNums[0];
            $prev = $monthNums[0];

            for ($i = 1; $i < count($monthNums); $i++) {
                if ($monthNums[$i] === $prev + 1) {
                    $prev = $monthNums[$i];
                } else {
                    $ranges[] = [$start, $prev];
                    $start = $monthNums[$i];
                    $prev = $monthNums[$i];
                }
            }
            $ranges[] = [$start, $prev];

            $parts = [];
            foreach ($ranges as [$rStart, $rEnd]) {
                if ($rStart === $rEnd) {
                    $parts[] = $shortMonthsMap[$rStart] ?? $rStart;
                } elseif ($rEnd === $rStart + 1) {
                    $parts[] = ($shortMonthsMap[$rStart] ?? $rStart) . ', ' . ($shortMonthsMap[$rEnd] ?? $rEnd);
                } else {
                    $parts[] = ($shortMonthsMap[$rStart] ?? $rStart) . '–' . ($shortMonthsMap[$rEnd] ?? $rEnd);
                }
            }

            return implode(', ', $parts);
        };

        $txCollection = isset($transactions) && $transactions instanceof \Illuminate\Support\Collection && $transactions->isNotEmpty()
            ? $transactions->values()
            : collect([$data]);

        $pages = $txCollection->chunk(3);
        $logoPath = public_path('assets/media/logos/logo-full.png');
        $hasLogoFile = file_exists($logoPath);
        $printedAt = \Carbon\Carbon::now()->locale('id_ID')->isoFormat('DD/MM/YYYY HH:mm');
    @endphp

    @foreach ($pages as $pageIndex => $pageTransactions)
    <div class="a4-sheet {{ !$loop->last ? 'page-break' : '' }}">
        @foreach ($pageTransactions->values() as $slipIndex => $tx)
        @php
            $details = $tx->transactionDetails ?? collect();
            $groupedDetails = collect();
            $isCompact = false;

            if ($tx->type !== 'SALDO' && $tx->type !== 'SAVING') {
                $groupedDetails = $details->groupBy(function ($detail) {
                    return $detail->bill?->billType?->name ?? 'Pembayaran Tagihan';
                });
                $isCompact = $groupedDetails->count() >= 4 || $details->count() >= 6;
            }

            $subTotal = 0;
            if ($tx->type === 'BILL') {
                $subTotal = $details->sum(function ($d) {
                    return ($d->amount !== null && $d->amount > 0) ? (int) $d->amount : (int) ($d->bill?->amount ?? 0);
                });
                if ($subTotal <= 0) {
                    $subTotal = (int) ($tx->pay_amount ?? 0);
                }
            } elseif ($tx->type === 'SAVING') {
                $subTotal = $details->sum(fn($d) => (int) ($d->savingHistory?->amount ?? 0)) ?: (int) ($tx->pay_amount ?? 0);
            } elseif ($tx->type === 'SALDO') {
                $subTotal = $details->sum(fn($d) => (int) ($d->saldoHistory?->amount ?? 0)) ?: (int) ($tx->pay_amount ?? 0);
            } else {
                $subTotal = (int) ($tx->pay_amount ?? 0);
            }

            $totalTagihan = (int) ($tx->pay_amount ?? $subTotal);
            if (($tx->xendit_fee ?? 0) > 0) {
                $totalTagihan += (int) $tx->xendit_fee + (int) ($tx->app_fee ?? 0);
            }
        @endphp

        <div class="receipt-slip {{ $slipIndex < 2 ? 'with-cutline' : '' }}">
            <!-- 1. Header -->
            <table class="header-table">
                <tr>
                    <td width="52%">
                        @if ($hasLogoFile)
                            <img src="{{ $logoPath }}" width="128" alt="Logo PPTQ Cahaya Tasbih" style="display: block;" />
                        @else
                            <div class="brand-title">PPTQ CAHAYA TASBIH</div>
                        @endif
                        <div class="brand-subtitle">PUSKOMINFO • Bukti Pembayaran Sah Sistem CT-Pay</div>
                    </td>
                    <td width="48%" class="text-end">
                        <div>
                            @if ($tx->status == 'PAID')
                                <span class="badge-pill-success">LUNAS</span>
                            @else
                                <span class="badge-pill-danger">BELUM LUNAS</span>
                            @endif
                            <span class="invoice-title">INVOICE BUKTI PEMBAYARAN</span>
                        </div>
                        <div class="invoice-code">#{{ $tx->payment_code }}</div>
                    </td>
                </tr>
            </table>

            <!-- 2. Student & Transaction Information Strip -->
            <div class="info-card">
                <table class="info-table">
                    <tr>
                        <td width="54%">
                            <table width="100%">
                                <tr>
                                    <td class="label-cell">Nama Santri</td>
                                    <td class="colon-cell">:</td>
                                    <td class="value-cell">
                                        {{ \Illuminate\Support\Str::limit($tx->student?->name ?? '-', 28) }}{{ $tx->student?->nis ? ' (' . $tx->student->nis . ')' : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell">Tgl Transaksi</td>
                                    <td class="colon-cell">:</td>
                                    <td class="value-cell">
                                        {{ \Carbon\Carbon::parse($tx->created_at)->locale('id_ID')->isoFormat('D MMMM YYYY, HH:mm') }} WIB
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="46%" style="border-left: 1px solid #E2E8F0; padding-left: 10px;">
                            <table width="100%">
                                <tr>
                                    <td class="label-cell" style="width: 68px;">Kelas / Unit</td>
                                    <td class="colon-cell">:</td>
                                    <td class="value-cell">
                                        {{ $tx->student?->classroom?->name ?? '-' }} • {{ \Illuminate\Support\Str::limit($tx->student?->classroom?->school?->name ?? '-', 22) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell" style="width: 68px;">Metode/Kasir</td>
                                    <td class="colon-cell">:</td>
                                    <td class="value-cell">
                                        {{ $tx?->paymentMethod?->name ?? 'Tunai' }} • {{ \Illuminate\Support\Str::limit($tx?->admin?->name ?? 'CT-PAY', 18) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 3. Main 2-Column Content (Left: Items Table, Right: Totals & QR Verification) -->
            <table class="main-layout-table">
                <tr>
                    <!-- Left Column: Rincian Pembayaran -->
                    <td width="66%" style="vertical-align: top; padding-right: 8px;">
                        <table class="data-table {{ $isCompact ? 'compact-mode' : '' }}">
                            @if ($tx->type == 'SALDO')
                                <thead>
                                    <tr>
                                        <th width="8%" class="text-center">No</th>
                                        <th width="42%">Nama Pembayaran</th>
                                        <th width="25%" class="text-end">Jumlah</th>
                                        <th width="25%" class="text-end">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($details->take(5) as $transaction_detail)
                                        <tr>
                                            <td class="text-center" style="color: #64748B; font-weight: 600;">{{ $loop->iteration }}</td>
                                            <td><span class="item-name">Top Up Saldo</span></td>
                                            <td class="text-end" style="font-weight: 700;">Rp {{ number_format($transaction_detail->saldoHistory?->amount ?? $tx->pay_amount ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end item-desc">{{ \Illuminate\Support\Str::limit($transaction_detail->saldoHistory?->description ?? '-', 28) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" style="color: #64748B; font-weight: 600;">1</td>
                                            <td><span class="item-name">Top Up Saldo</span></td>
                                            <td class="text-end" style="font-weight: 700;">Rp {{ number_format($tx->pay_amount ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end item-desc">-</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            @elseif ($tx->type == 'SAVING')
                                <thead>
                                    <tr>
                                        <th width="8%" class="text-center">No</th>
                                        <th width="42%">Nama Pembayaran</th>
                                        <th width="25%" class="text-end">Jumlah</th>
                                        <th width="25%" class="text-end">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($details->take(5) as $transaction_detail)
                                        <tr>
                                            <td class="text-center" style="color: #64748B; font-weight: 600;">{{ $loop->iteration }}</td>
                                            <td><span class="item-name">Setoran Tabungan</span></td>
                                            <td class="text-end" style="font-weight: 700;">Rp {{ number_format($transaction_detail->savingHistory?->amount ?? $tx->pay_amount ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end item-desc">{{ \Illuminate\Support\Str::limit($transaction_detail->savingHistory?->description ?? '-', 28) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" style="color: #64748B; font-weight: 600;">1</td>
                                            <td><span class="item-name">Setoran Tabungan</span></td>
                                            <td class="text-end" style="font-weight: 700;">Rp {{ number_format($tx->pay_amount ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end item-desc">-</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            @else
                                @php
                                    $maxVisibleGroups = 5;
                                    $visibleGroups = $groupedDetails->take($maxVisibleGroups);
                                    $overflowGroups = $groupedDetails->slice($maxVisibleGroups);
                                @endphp
                                <thead>
                                    <tr>
                                        <th width="7%" class="text-center">No</th>
                                        <th width="38%">Item Tagihan</th>
                                        <th width="33%">Periode (Bulan & Tahun)</th>
                                        <th width="22%" class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($visibleGroups as $billTypeName => $detailsGroup)
                                        @php
                                            $groupTotal = $detailsGroup->sum(function ($d) {
                                                return ($d->amount !== null && $d->amount > 0) ? (int) $d->amount : (int) ($d->bill?->amount ?? 0);
                                            });
                                            $byYear = $detailsGroup->groupBy(function ($d) {
                                                return $d->bill?->year ?? ($d->bill?->academicYear?->name ?? '-');
                                            })->sortKeys();
                                        @endphp
                                        <tr>
                                            <td class="text-center" style="color: #64748B; font-weight: 600;">{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="item-name">{{ \Illuminate\Support\Str::limit($billTypeName, 26) }}</span>
                                                @if ($detailsGroup->count() > 1)
                                                    <span class="item-count-badge">({{ $detailsGroup->count() }} Bln)</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach ($byYear as $year => $yearItems)
                                                    <div class="period-year-row">
                                                        @if ($year !== '-')
                                                            <span class="period-year-badge">{{ $year }}</span>
                                                        @endif
                                                        <span class="period-months-text">{{ $formatSmartMonths($yearItems, $isCompact || $byYear->count() > 1) }}</span>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="text-end" style="font-weight: 700; color: #0F172A;">
                                                Rp {{ number_format($groupTotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" style="color: #64748B; font-weight: 600;">1</td>
                                            <td><span class="item-name">Pembayaran Tagihan</span></td>
                                            <td>-</td>
                                            <td class="text-end" style="font-weight: 700; color: #0F172A;">Rp {{ number_format($tx->pay_amount ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforelse

                                    @if ($overflowGroups->isNotEmpty())
                                        @php
                                            $overflowTotal = $overflowGroups->sum(function ($grp) {
                                                return $grp->sum(fn($d) => ($d->amount !== null && $d->amount > 0) ? (int) $d->amount : (int) ($d->bill?->amount ?? 0));
                                            });
                                            $overflowItemCount = $overflowGroups->sum(fn($grp) => $grp->count());
                                        @endphp
                                        <tr>
                                            <td class="text-center" style="color: #64748B; font-weight: 600;">{{ $visibleGroups->count() + 1 }}</td>
                                            <td>
                                                <span class="item-name">+{{ $overflowGroups->count() }} Jenis Tagihan Lainnya</span>
                                            </td>
                                            <td>
                                                <span class="period-months-text">{{ $overflowItemCount }} Item Terbayar</span>
                                            </td>
                                            <td class="text-end" style="font-weight: 700; color: #0F172A;">
                                                Rp {{ number_format($overflowTotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            @endif
                        </table>
                    </td>

                    <!-- Right Column: Summary Totals & Digital QR Verification -->
                    <td width="34%" style="vertical-align: top;">
                        <div class="summary-panel">
                            <table class="summary-table">
                                <tr>
                                    <td class="summary-label">Sub Total ({{ max(1, $details->count()) }} Item)</td>
                                    <td class="summary-value">Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                                </tr>

                                @if (($tx->xendit_fee ?? 0) > 0)
                                <tr>
                                    <td class="summary-label">Biaya Transaksi</td>
                                    <td class="summary-value">Rp {{ number_format($tx->xendit_fee, 0, ',', '.') }}</td>
                                </tr>
                                @endif

                                @if (($tx->app_fee ?? 0) > 0)
                                <tr>
                                    <td class="summary-label">Biaya Aplikasi</td>
                                    <td class="summary-value">Rp {{ number_format($tx->app_fee, 0, ',', '.') }}</td>
                                </tr>
                                @endif

                                @if (($tx->unique_payment ?? 0) > 0)
                                <tr>
                                    <td class="summary-label">Kode Unik</td>
                                    <td class="summary-value">Rp {{ number_format($tx->unique_payment, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            </table>

                            <div class="total-box">
                                <table class="total-box-table">
                                    <tr>
                                        <td class="total-label">TOTAL BAYAR</td>
                                        <td class="total-value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="verification-divider">
                                <table width="100%" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="vertical-align: middle; padding-right: 4px;" class="verification-note">
                                            <strong>Bukti Sah Sistem CT-Pay</strong><br />
                                            Petugas: <strong>{{ \Illuminate\Support\Str::limit($tx?->admin?->name ?? 'CT-PAY', 18) }}</strong><br />
                                            Cetak: {{ $printedAt }} WIB<br />
                                            <a href="https://cahayatasbih.or.id/" target="_blank">PUSKOMINFO CAHAYA TASBIH</a>
                                        </td>
                                        <td width="46" class="text-end" style="vertical-align: middle;">
                                            <img src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(88)->margin(0)->generate(route('transaction.invoice', $tx->id))) !!}"
                                                alt="QR" style="display: inline-block; width: 44px; height: 44px;" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            @if ($slipIndex < 2)
                <div class="cut-indicator">✂ Potong di sini (99 mm)</div>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</body>

</html>
