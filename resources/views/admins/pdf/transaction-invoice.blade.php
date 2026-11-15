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
            font-family: 'Inter', Helvetica, Arial, sans-serif !important;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            color: #1E293B;
            font-size: 9.5px;
            line-height: 1.25;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        .a4-sheet {
            width: 100%;
            position: relative;
        }

        .page-break {
            page-break-after: always;
        }

        /*
         * 1/3 A4 Slip Container:
         * PENTING UNTUK DOMPDF: Jangan gunakan width: 210mm bersamaan dengan padding horizontal,
         * karena DomPDF mengabaikan box-sizing: border-box dan menjumlahkan 210mm + padding = 228mm (terpotong di kanan).
         * Gunakan width: auto dengan padding kiri-kanan 10mm sehingga lebar bersih otomatis 190mm di tengah kertas A4.
         */
        .receipt-slip {
            width: auto;
            height: 86.5mm;
            max-height: 86.5mm;
            padding: 9mm 10mm 2.5mm 10mm;
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
        }

        .receipt-slip.compact-slip {
            height: 90mm;
            max-height: 90mm;
            padding: 5.5mm 10mm 2.5mm 10mm;
        }

        .receipt-slip.with-cutline {
            border-bottom: 1.2px dashed #94A3B8;
        }

        /* Bingkai Kartu Kwitansi per Slip */
        .slip-card {
            width: auto;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            padding: 7.5px 10px 6.5px 10px;
            background-color: #FFFFFF;
        }

        .cut-indicator {
            position: absolute;
            bottom: 1px;
            right: 10mm;
            font-size: 7px;
            color: #64748B;
            font-weight: 600;
            background-color: #F8FAFC;
            padding: 0px 5px;
            border-radius: 3px;
        }

        /* Header */
        .header-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 6px;
            padding-bottom: 4.5px;
            border-bottom: 1.5px solid #E2E8F0;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-title {
            font-size: 12.5px;
            font-weight: 800;
            color: #4D0C7A;
            letter-spacing: 0.2px;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 8px;
            color: #64748B;
            font-weight: 600;
            margin-top: 1px;
        }

        .invoice-title {
            font-size: 10.5px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.2px;
            text-transform: uppercase;
            display: inline-block;
            vertical-align: middle;
        }

        .invoice-code {
            font-size: 9.5px;
            font-weight: 700;
            color: #4D0C7A;
            margin-top: 1px;
        }

        /* Badge Status */
        .badge-pill-success {
            background-color: #DEF7EC;
            color: #03543F;
            padding: 1px 6px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 8px;
            letter-spacing: 0.2px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 3px;
        }

        .badge-pill-danger {
            background-color: #FDE8E8;
            color: #9B1C1C;
            padding: 1px 6px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 8px;
            letter-spacing: 0.2px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 3px;
        }

        /* Student & Transaction Info Compact Box */
        .info-card {
            width: auto;
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 5px;
            padding: 5.5px 9px;
            margin-bottom: 6.5px;
        }

        .info-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 0.8px 0;
            font-size: 8.8px;
            vertical-align: top;
            line-height: 1.25;
        }

        .label-cell {
            color: #475569;
            font-weight: 600;
            white-space: nowrap;
        }

        .colon-cell {
            color: #475569;
            text-align: center;
            white-space: nowrap;
        }

        .value-cell {
            color: #0F172A;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Main 2-Column Layout Table (64% Left : 36% Right) */
        .main-layout-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        /* Data Table (Left Column) */
        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #CBD5E1;
            border-radius: 5px;
            overflow: hidden;
        }

        .data-table thead tr {
            background-color: #F1F5F9;
        }

        .data-table thead th {
            color: #334155;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 3.5px 6px;
            border-bottom: 1px solid #CBD5E1;
            text-align: left;
        }

        .data-table tbody td {
            padding: 3.5px 6px;
            font-size: 8.8px;
            color: #1E293B;
            vertical-align: top;
            border-bottom: 1px solid #F1F5F9;
            line-height: 1.2;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Auto-Compact Mode when >= 4 BillType groups */
        .data-table.compact-mode thead th {
            padding: 2.5px 5px;
            font-size: 7.5px;
        }

        .data-table.compact-mode tbody td {
            padding: 2px 5px;
            font-size: 8px;
            line-height: 1.12;
        }

        .item-name {
            font-weight: 700;
            color: #4D0C7A;
        }

        .item-count-badge {
            font-size: 7.5px;
            color: #475569;
            font-weight: 600;
            display: inline-block;
            margin-left: 2px;
        }

        .period-year-row {
            margin-bottom: 1px;
            line-height: 1.15;
        }

        .period-year-row:last-child {
            margin-bottom: 0;
        }

        .period-year-badge {
            background-color: #E2E8F0;
            color: #1E293B;
            font-weight: 700;
            font-size: 7.5px;
            padding: 0.5px 3.5px;
            border-radius: 3px;
            display: inline-block;
            margin-right: 2px;
        }

        .period-months-text {
            font-size: 8px;
            color: #334155;
            font-weight: 600;
        }

        .item-desc {
            color: #475569;
            font-size: 8px;
        }

        /* Right Summary & Verification Panel */
        .summary-panel {
            width: auto;
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 5px;
            padding: 5px 7px;
        }

        .summary-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 1.5px 1px;
            font-size: 8.5px;
        }

        .summary-label {
            color: #475569;
            font-weight: 600;
        }

        .summary-value {
            text-align: right;
            color: #0F172A;
            font-weight: 700;
            white-space: nowrap;
        }

        .total-box {
            width: auto;
            background-color: #ECFDF5;
            border: 1px solid #A7F3D0;
            border-radius: 4px;
            margin-top: 2.5px;
            padding: 4px 6px;
        }

        .total-box-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .total-label {
            color: #065F46;
            font-weight: 800;
            font-size: 8.8px;
            text-transform: uppercase;
        }

        .total-value {
            color: #059669;
            font-weight: 800;
            font-size: 10px;
            text-align: right;
            white-space: nowrap;
        }

        .verification-divider {
            border-top: 1px solid #E2E8F0;
            margin-top: 4.5px;
            padding-top: 4px;
        }

        .verification-note {
            font-size: 7.5px;
            color: #475569;
            line-height: 1.25;
        }

        .verification-note strong {
            color: #0F172A;
            font-weight: 700;
        }

        .role-badge {
            display: inline-block;
            margin-top: 1.5px;
            background-color: #F3E8FF;
            color: #4D0C7A;
            border: 1px solid #D8B4FE;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.2px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .slip-footer-note {
            margin-top: 3.5px;
            padding-top: 2.5px;
            border-top: 1px solid #F1F5F9;
            font-size: 7px;
            color: #94A3B8;
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

            if (!$useCompact && count($monthNums) <= 3) {
                return $sortedItems->map(fn($d) => $d->bill?->translated_month ?? ($shortMonthsMap[(int)$d->bill?->month] ?? ''))->filter()->unique()->implode(', ');
            }

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

        $normalizePhone = function (?string $raw): ?string {
            if (empty($raw)) {
                return null;
            }
            $digits = preg_replace('/\D/', '', $raw);
            if ($digits === '' || strlen($digits) < 8) {
                return null;
            }
            if (str_starts_with($digits, '0')) {
                return '62' . substr($digits, 1);
            }
            if (str_starts_with($digits, '8')) {
                return '62' . $digits;
            }
            return $digits;
        };

        $resolvePrimaryRoleLabel = function ($admin, string $uptType): string {
            if (!$admin) {
                return $uptType !== '' ? 'BENDAHARA ' . $uptType : 'SISTEM CT-PAY';
            }

            $roleNames = $admin->roles ? $admin->roles->pluck('name')->map(fn($r) => trim($r))->filter()->values()->all() : [];
            if (empty($roleNames) && !empty($admin->role_name)) {
                $roleNames = array_filter(array_map('trim', explode(',', $admin->role_name)));
            }

            foreach ($roleNames as $rName) {
                if (stripos($rName, 'bendahara') !== false) {
                    return strtoupper($rName);
                }
            }
            foreach ($roleNames as $rName) {
                if (stripos($rName, 'piket') !== false) {
                    return strtoupper($rName);
                }
            }
            foreach ($roleNames as $rName) {
                if (stripos($rName, 'tata usaha') !== false || preg_match('/\btu\b/i', $rName)) {
                    return strtoupper($rName);
                }
            }

            $firstRole = $roleNames[0] ?? 'PETUGAS KEUANGAN';
            return strtoupper(\Illuminate\Support\Str::limit($firstRole, 24, ''));
        };

        $txCollection = isset($transactions) && $transactions instanceof \Illuminate\Support\Collection && $transactions->isNotEmpty()
            ? $transactions->values()
            : collect([$data]);

        $waMap = $uptWaNumbers ?? [
            'SMP' => '6281225219812',
            'MA' => '6285728128483',
            'PONDOK' => null,
            'DEFAULT' => '6281326368494',
        ];

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

            // Identifikasi UPT (SMP / MA / PONDOK) & Role Petugas
            $schoolType = strtoupper(trim($tx->student?->classroom?->school?->type ?? ''));
            if (!in_array($schoolType, ['SMP', 'MA', 'PONDOK'], true)) {
                $schoolNameUpper = strtoupper($tx->student?->classroom?->school?->name ?? '');
                if (str_contains($schoolNameUpper, 'SMP')) {
                    $schoolType = 'SMP';
                } elseif (str_contains($schoolNameUpper, 'MA') || str_contains($schoolNameUpper, 'ALIYAH')) {
                    $schoolType = 'MA';
                } else {
                    $schoolType = 'PONDOK';
                }
            }

            $officerRoleLabel = $resolvePrimaryRoleLabel($tx->admin, $schoolType);
            $officerPhone = $normalizePhone($tx->admin?->phone);

            if ($schoolType === 'SMP') {
                $targetWaPhone = $waMap['SMP'] ?? $officerPhone ?? $waMap['DEFAULT'];
            } elseif ($schoolType === 'MA') {
                $targetWaPhone = $waMap['MA'] ?? $officerPhone ?? $waMap['DEFAULT'];
            } else {
                $targetWaPhone = $waMap['PONDOK'] ?? $officerPhone ?? $waMap['DEFAULT'];
            }

            $studentShortName = \Illuminate\Support\Str::limit($tx->student?->name ?? 'Santri', 24, '');
            $formattedTotalRp = 'Rp ' . number_format($totalTagihan, 0, ',', '.');
            $waText = "Konfirmasi Bukti Bayar #{$tx->payment_code} a.n {$studentShortName} ({$formattedTotalRp})";
            $waQrLink = 'https://wa.me/' . $targetWaPhone . '?text=' . rawurlencode($waText);
        @endphp

        <div class="receipt-slip {{ $slipIndex < 2 ? 'with-cutline' : '' }} {{ $isCompact ? 'compact-slip' : '' }}">
            <div class="slip-card">
                <!-- 1. Header -->
                <table class="header-table">
                    <tr>
                        <td width="50%">
                            @if ($hasLogoFile)
                                <img src="{{ $logoPath }}" width="118" alt="Logo PPTQ Cahaya Tasbih" style="display: block;" />
                            @else
                                <div class="brand-title">PPTQ CAHAYA TASBIH</div>
                            @endif
                            <div class="brand-subtitle">PPTQ Cahaya Tasbih • Bukti Pembayaran Sah</div>
                        </td>
                        <td width="50%" class="text-end">
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
                            <td width="56%" style="padding-right: 6px;">
                                <table width="100%" style="table-layout: fixed; border-collapse: collapse;">
                                    <tr>
                                        <td width="23%" class="label-cell">Nama Santri</td>
                                        <td width="3%" class="colon-cell">:</td>
                                        <td width="74%" class="value-cell">
                                            {{ \Illuminate\Support\Str::limit($tx->student?->name ?? '-', 26) }}{{ $tx->student?->nis ? ' (' . $tx->student->nis . ')' : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="23%" class="label-cell">Tgl Transaksi</td>
                                        <td width="3%" class="colon-cell">:</td>
                                        <td width="74%" class="value-cell">
                                            {{ \Carbon\Carbon::parse($tx->created_at)->locale('id_ID')->isoFormat('D MMMM YYYY, HH:mm') }} WIB
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="44%" style="border-left: 1px solid #E2E8F0; padding-left: 8px;">
                                <table width="100%" style="table-layout: fixed; border-collapse: collapse;">
                                    <tr>
                                        <td width="26%" class="label-cell">Kelas / Unit</td>
                                        <td width="3%" class="colon-cell">:</td>
                                        <td width="71%" class="value-cell">
                                            {{ $tx->student?->classroom?->name ?? '-' }} • {{ \Illuminate\Support\Str::limit($tx->student?->classroom?->school?->name ?? '-', 22) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="26%" class="label-cell">Metode/Kasir</td>
                                        <td width="3%" class="colon-cell">:</td>
                                        <td width="71%" class="value-cell">
                                            {{ $tx?->paymentMethod?->name ?? 'Tunai' }} • {{ \Illuminate\Support\Str::limit($tx?->admin?->name ?? 'CT-PAY', 20) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- 3. Main 2-Column Content (Left 64%: Items Table, Right 36%: Totals & QR WA Bendahara UPT) -->
                <table class="main-layout-table">
                    <tr>
                        <!-- Left Column: Rincian Pembayaran -->
                        <td width="64%" style="vertical-align: top; padding-right: 7px;">
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
                                                <td class="text-end" style="font-weight: 700; white-space: nowrap;">Rp {{ number_format($transaction_detail->saldoHistory?->amount ?? $tx->pay_amount ?? 0, 0, ',', '.') }}</td>
                                                <td class="text-end item-desc">{{ \Illuminate\Support\Str::limit($transaction_detail->saldoHistory?->description ?? '-', 24) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" style="color: #64748B; font-weight: 600;">1</td>
                                                <td><span class="item-name">Top Up Saldo</span></td>
                                                <td class="text-end" style="font-weight: 700; white-space: nowrap;">Rp {{ number_format($tx->pay_amount ?? 0, 0, ',', '.') }}</td>
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
                                                <td class="text-end" style="font-weight: 700; white-space: nowrap;">Rp {{ number_format($transaction_detail->savingHistory?->amount ?? $tx->pay_amount ?? 0, 0, ',', '.') }}</td>
                                                <td class="text-end item-desc">{{ \Illuminate\Support\Str::limit($transaction_detail->savingHistory?->description ?? '-', 24) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" style="color: #64748B; font-weight: 600;">1</td>
                                                <td><span class="item-name">Setoran Tabungan</span></td>
                                                <td class="text-end" style="font-weight: 700; white-space: nowrap;">Rp {{ number_format($tx->pay_amount ?? 0, 0, ',', '.') }}</td>
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
                                            <th width="39%">Item Tagihan</th>
                                            <th width="31%">Periode (Bulan & Tahun)</th>
                                            <th width="23%" class="text-end">Nominal</th>
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
                                                    <span class="item-name">{{ \Illuminate\Support\Str::limit($billTypeName, 24) }}</span>
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
                                                <td class="text-end" style="font-weight: 700; color: #0F172A; white-space: nowrap;">
                                                    Rp {{ number_format($groupTotal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" style="color: #64748B; font-weight: 600;">1</td>
                                                <td><span class="item-name">Pembayaran Tagihan</span></td>
                                                <td>-</td>
                                                <td class="text-end" style="font-weight: 700; color: #0F172A; white-space: nowrap;">Rp {{ number_format($tx->pay_amount ?? 0, 0, ',', '.') }}</td>
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
                                                <td class="text-end" style="font-weight: 700; color: #0F172A; white-space: nowrap;">
                                                    Rp {{ number_format($overflowTotal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                @endif
                            </table>
                        </td>

                        <!-- Right Column: Summary Totals, Officer Role & WA Bendahara UPT QR Code -->
                        <td width="36%" style="vertical-align: top;">
                            <div class="summary-panel">
                                <table class="summary-table">
                                    <tr>
                                        <td width="52%" class="summary-label">Sub Total ({{ max(1, $details->count()) }} Item)</td>
                                        <td width="48%" class="summary-value">Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
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
                                            <td width="44%" class="total-label">TOTAL BAYAR</td>
                                            <td width="56%" class="total-value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="verification-divider">
                                    <table width="100%" style="table-layout: fixed; border-collapse: collapse;">
                                        <tr>
                                            <td width="76%" style="vertical-align: middle; padding-right: 4px;" class="verification-note">
                                                <strong>Bukti Sah</strong><br />
                                                Petugas: <strong>{{ \Illuminate\Support\Str::limit($tx?->admin?->name ?? 'CT-PAY', 18) }}</strong><br />
                                                Cetak: {{ $printedAt }} WIB<br />
                                                <span class="role-badge">{{ $officerRoleLabel }}</span>
                                            </td>
                                            <td width="24%" class="text-end" style="vertical-align: middle;">
                                                <a href="{{ $waQrLink }}" target="_blank" style="text-decoration: none;">
                                                    <img src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(84)->margin(0)->generate($waQrLink)) !!}"
                                                        alt="QR WA Bendahara" style="display: inline-block; width: 41px; height: 41px;" />
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="slip-footer-note">
                    <table width="100%" style="table-layout: fixed; border-collapse: collapse;">
                        <tr>
                            <td width="72%">Kwitansi Resmi • Scan QR untuk Konfirmasi WA Bendahara UPT ({{ $schoolType }})</td>
                            <td width="28%" class="text-end">Format 1/3 A4 (210 × 99 mm)</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if ($slipIndex < 2)
                <div class="cut-indicator">✂ Potong di sini (99 mm)</div>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</body>

</html>
