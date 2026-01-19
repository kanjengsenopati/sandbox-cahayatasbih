<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kelas</th>
            <th>Nama Siswa</th>
            @foreach($periods as $period)
                <th>{{ $period['label'] }}</th>
            @endforeach
            <th>Tagihan Berjalan</th>
            <th>Total Kekurangan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->classroom->name ?? '-' }}</td>
                <td>{{ $student->name }}</td>
                @foreach($periods as $period)
                    @php
                        $bill = $student->bills->filter(function($b) use ($period, $billTypeId) {
                            return $b->month == $period['month'] && 
                                   $b->year == $period['year'] && 
                                   $b->bill_type_id == $billTypeId;
                        })->first();
                        
                        // Logic: Tampilkan Sisa Tagihan.
                        // Jika Lunas (Status PAID) -> Sisa 0 -> Tampilan Kosong.
                        // Jika Belum Lunas (Status UNPAID) -> Sisa = Amount -> Tampilan Amount.
                        
                        $remaining = 0;
                        if ($bill) {
                            if ($bill->status != 'PAID') { // Asumsi ada konstanta PAID di model, tapi string 'PAID' juga works
                                $remaining = $bill->amount;
                            }
                        }
                    @endphp
                    <td>{{ $remaining > 0 ? $remaining : '' }}</td>
                @endforeach
                <td>{{ $student->current_due > 0 ? $student->current_due : '' }}</td>
                <td>{{ $student->total_unpaid }}</td>
                <td>{{ $student->total_unpaid > 0 ? 'BELUM LUNAS' : 'LUNAS' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
