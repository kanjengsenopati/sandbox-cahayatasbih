<div class="card-body pt-3">
    <!--begin::Card Information-->
    <div class="card-information">
        <div class="mb-3">
            <span class="fw-bold text-muted">Tahun Ajaran</span>
            :&nbsp;
            <span><b>Semua Tahun Ajaran</b></span>
        </div>
        <div class="mb-3">
            <span class="fw-bold text-muted">NISN</span>
            :&nbsp;
            <span>{{ @$student->nisn ?? '' }}</span>
        </div>
        <div class="mb-3">
            <span class="fw-bold text-muted">Nama</span>
            :&nbsp;
            <span>{{ @$student->name ?? '' }}</span>
        </div>
        <div class="mb-3">
            <span class="fw-bold text-muted">Kelas</span>
            :&nbsp;
            <span>{{ @$student->classroom->name ?? '' }}</span>
        </div>
    </div>
    <!--end::Card Information-->

    <!--begin::Separator-->
    <div class="separator mb-6"></div>
    <!--end::Separator-->

    <!--begin::Action buttons-->
    <div class="d-flex justify-content-end">
    </div>
    <!--end::Action buttons-->
</div>

<!--begin::Accordion-->
<div id="kt_accordion_1" class="accordion accordion-flush mx-5">
    <div class="accordion-item">
        <h2 class="accordion-header" id="kt_accordion_1_header_1">
            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse"
                data-bs-target="#kt_accordion_1_body_1" aria-expanded="true" aria-controls="kt_accordion_1_body_1">
                Fitur Kilat
            </button>
        </h2>
        <div id="kt_accordion_1_body_1" class="accordion-collapse collapse show"
            aria-labelledby="kt_accordion_1_header_1" data-bs-parent="#kt_accordion_1">
            <div class="accordion-body">

                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_4">Bulanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_5">Lainnya</a>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="kt_tab_pane_4" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped gy-7 gs-7">
                                <thead>
                                    <tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">
                                        <th>No</th>
                                        <th class="min-w-125px">Nama Tagihan</th>
                                        <th class="min-w-125px">Sisa Tagihan</th>
                                        @foreach (range(1, 12) as $month)
                                        <th class="min-w-125px">{{
                                            \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</th>
                                        @endforeach

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($billMonth as $bill)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ @$bill->name }}</td>
                                        <td>Rp {{ number_format(@$bill->total_unpaid, 0, ',', '.') }}</td>
                                        @foreach (range(1, 12) as $month)
                                        @php
                                        $amount = @$bill->bills->where('month', $month)->first()->amount ?? 0;
                                        @endphp
                                        <td>Rp
                                            {{
                                            number_format($amount, 0, ',', '.')
                                            }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped gy-7 gs-7">
                                <thead>
                                    <tr class="fw-semibold fs-6 text-gray-800 border-bottom border-gray-200">
                                        <th>No</th>
                                        <th class="min-w-125px">Nama Tagihan</th>
                                        <th class="min-w-125px">Sisa Tagihan</th>
                                        @foreach (range(1, 12) as $month)
                                        <th class="min-w-125px">{{
                                            \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($billOthers as $bill)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ @$bill->name }}</td>
                                        <td>Rp {{ number_format(@$bill->total_unpaid, 0, ',', '.') }}</td>
                                        @foreach (range(1, 12) as $month)
                                        @php
                                        $amount = @$bill->bills->where('month', $month)->first()->amount ?? 0;
                                        @endphp
                                        <td>Rp {{ number_format($amount, 0, ',', '.') }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="separator mb-6"></div>
    <div class="accordion-item">
        <h2 class="accordion-header" id="kt_accordion_1_header_1">
            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse"
                data-bs-target="#accordion-tagihan-bulanan" aria-expanded="true"
                aria-controls="accordion-tagihan-bulanan">
                Tagihan Bulanan
            </button>
        </h2>
        <div id="accordion-tagihan-bulanan" class="accordion-collapse collapse show"
            aria-labelledby="kt_accordion_1_header_1" data-bs-parent="#kt_accordion_1">
            <div class="accordion-body">
                <div class="table-responsive">
                    <table id="table-bill-monthly" class="table align-middle table-row-dashed ">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th style="width: 5%">No</th>
                                <th class="min-w-125px">Tahun Ajaran</th>
                                <th class="min-w-125px">Pos Bayar</th>
                                <th class="min-w-125px">Jenis Pembayaran</th>
                                <th class="min-w-125px">Jumlah</th>
                                <th class="min-w-125px">Dibayar</th>
                                <th class="text-center min-w-100px" style="width: 22%">Status</th>
                                <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-bold">
                            @foreach ($billMonth as $monthly)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ @$monthly->academicYear->name }}</td>
                                <td>{{ @$monthly->billItem->name }}</td>
                                <td>{{ @$monthly->name }}</td>
                                <td>Rp {{ number_format(@$monthly->total_unpaid, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format(@$monthly->total_paid, 0, ',', '.') }}</td>
                                <td><span class="d-flex text-center bg-{{ @$monthly->total_unpaid == 0 ?
                                         'success' : 'danger' }} text-white px-3 py-1 rounded-1">{{
                                        @$monthly->total_unpaid == 0 ? 'Lunas' : 'Belum Lunas' }}</span></td>
                                <td class="text-center">
                                    <x-action.show
                                        :action="route('bill.summary-bill', ['bill_type_id' => $monthly->id, 'student_id' => $student->id])"
                                        label="Bayar" />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="separator mb-6"></div>
    <div class="accordion-item">
        <h2 class="accordion-header" id="kt_accordion_1_header_1">
            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse"
                data-bs-target="#accordion-bill-other" aria-expanded="true" aria-controls="accordion-bill-other">
                Tagihan Lainnya
            </button>
        </h2>
        <div id="accordion-bill-other" class="accordion-collapse collapse show"
            aria-labelledby="kt_accordion_1_header_1" data-bs-parent="#kt_accordion_1">
            <div class="accordion-body">
                <div class="table-responsive">
                    <table id="table-bill-monthly" class="table align-middle table-row-dashed ">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th style="width: 5%">No</th>
                                <th class="min-w-125px">Tahun Ajaran</th>
                                <th class="min-w-125px">Pos Bayar</th>
                                <th class="min-w-125px">Jenis Pembayaran</th>
                                <th class="min-w-125px">Jumlah</th>
                                <th class="min-w-125px">Dibayar</th>
                                <th class="text-center min-w-100px" style="width: 22%">Status</th>
                                <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-bold">
                            @foreach ($billOthers as $other)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ @$other->academicYear->name }}</td>
                                <td>{{ @$other->billItem->name }}</td>
                                <td>{{ @$other->name }}</td>
                                <td>Rp {{ number_format(@$other->total_unpaid, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format(@$other->total_paid, 0, ',', '.') }}</td>
                                <td><span class="d-flex text-center bg-{{ @$other->total_unpaid == 0 ?
                                             'success' : 'danger' }} text-white px-3 py-1 rounded-1">{{
                                        @$other->total_unpaid == 0 ? 'Lunas' : 'Belum Lunas' }}</span></td>
                                <td class="text-center">
                                    <x-action.show
                                        :action="route('bill.summary-bill', ['bill_type_id' => $other->id, 'student_id' => $student->id])"
                                        label="Bayar" />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>