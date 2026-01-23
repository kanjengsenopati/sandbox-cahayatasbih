<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->payment_code }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px; /* Adjusted for smaller thermal width */
            margin: 5px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 9px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .meta-table, .item-table, .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .item-row td {
            padding-top: 2px;
            padding-bottom: 2px;
            vertical-align: top;
        }
        
        .totals-row td {
            padding-top: 4px;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>KOPERASI CAHAYA TASBIH</h2>
        <p>Jalan Raya Demak - Kudus KM. 14</p>
        <p>Telp: 0812-3456-7890</p>
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td>No: {{ $order->payment_code }}</td>
            <td class="text-right">{{ $order->created_at->format('d/m/y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir: {{ $order->admins->name ?? 'Admin' }}</td>
            <td class="text-right">{{ $order->type }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="item-table">
        @foreach($order->pointOfSaleTransactionDetails as $detail)
        <tr class="item-row">
            <td colspan="2" style="padding-bottom: 0;">{{ $detail->item->name ?? 'Item' }}</td>
        </tr>
        <tr class="item-row">
            <td style="padding-left: 10px;">{{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($detail->total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table class="total-table">
        <tr class="totals-row">
            <td>TOTAL</td>
            <td class="text-right">{{ number_format($order->pay_amount, 0, ',', '.') }}</td>
        </tr>

        @if($order->type === 'SANTRI' && $order->saldoHistory && $order->saldoHistory->balance_before)
             {{-- LOGIKA PEMBAYARAN VIA SALDO SANTRI --}}
            <tr>
                <td>BAYAR (SALDO)</td>
                <td class="text-right">{{ number_format($order->pay_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div class="divider"></div>

        <table class="total-table">
            <tr>
                <td colspan="2" style="padding-bottom: 2px;">DETAIL SALDO SANTRI</td>
            </tr>
            <tr>
                <td>Saldo Awal</td>
                <td class="text-right">{{ number_format($order->saldoHistory->balance_before ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pengurangan</td>
                <td class="text-right">-{{ number_format($order->pay_amount, 0, ',', '.') }}</td>
            </tr>
             <tr>
                <td style="font-weight: bold;">Saldo Akhir</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($order->saldoHistory->balance_after ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
        @else
             {{-- LOGIKA PEMBAYARAN UMUM / TUNAI BIASA --}}
            <tr>
                <td>TUNAI</td>
                <td class="text-right">{{ number_format($order->pay_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>KEMBALI</td>
                <td class="text-right">0</td>
            </tr>
        </table>
        @endif

    <div class="divider"></div>

    <div class="footer">
        <p>TERIMA KASIH</p>
        <p>Semoga Berkah</p>
        <p>Barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan</p>
    </div>
</body>
</html>
