<tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">
    <th>No</th>
    <th class="min-w-125px">Nama Tagihan</th>
    <th class="min-w-125px">Sisa Tagihan</th>
    @foreach (range(1, 12) as $month)
    <th class="min-w-125px">{{
        \Carbon\Carbon::create()->month($month)->translatedFormat('F')
        }}</th>
    @endforeach
</tr>