<!--begin::Filters Toolbar-->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 my-4">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <h4 class="text-dark fw-bolder mb-0">Arsip Riwayat Topup Saldo</h4>
        <!--begin::Period Presets-->
        <div class="btn-group btn-group-sm" role="group" id="archive-period-group">
            <button type="button" class="btn btn-light-primary archive-period-btn" data-period="today">Hari Ini</button>
            <button type="button" class="btn btn-primary archive-period-btn active" data-period="week">7 Hari Terakhir</button>
            <button type="button" class="btn btn-light-primary archive-period-btn" data-period="month">Bulan Ini</button>
            <button type="button" class="btn btn-light-primary archive-period-btn" data-period="custom">Cari Sendiri</button>
        </div>
        <!--end::Period Presets-->
    </div>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <label class="fs-7 fw-bold text-gray-700 mb-0">Cari Siswa:</label>
            <input type="text" id="archive-search-name" class="form-control form-control-solid form-control-sm" placeholder="Nama Siswa / NIS..." style="width: 170px;">
        </div>
        <!--begin::Custom Date Range Container-->
        <div class="d-flex align-items-center gap-2" id="archive-custom-date-container">
            <div class="d-flex align-items-center gap-1">
                <label class="fs-7 fw-bold text-gray-700 mb-0">Mulai:</label>
                <input type="date" id="archive-start-date" class="form-control form-control-solid form-control-sm" value="{{ now()->subDays(6)->format('Y-m-d') }}" style="width: 140px;">
            </div>
            <div class="d-flex align-items-center gap-1">
                <label class="fs-7 fw-bold text-gray-700 mb-0">Selesai:</label>
                <input type="date" id="archive-end-date" class="form-control form-control-solid form-control-sm" value="{{ now()->format('Y-m-d') }}" style="width: 140px;">
            </div>
        </div>
        <!--end::Custom Date Range Container-->
        <button id="archive-btn-filter" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
        <button id="archive-btn-reset" class="btn btn-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</button>
    </div>
</div>
<!--end::Filters Toolbar-->

<!--begin::Table-->
<div class="table-responsive">
    <table id="table-archive" class="table align-middle table-row-dashed" style="width: 100%;">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th>Siswa</th>
                <th>Nominal</th>
                <th>Kode Unik</th>
                <th>Bank Tujuan</th>
                <th>Bukti Transfer</th>
                <th>Status</th>
                <th>Petugas</th>
                <th>Tanggal & Waktu</th>
                <th class="text-center min-w-100px" style="width: 12%">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-bold"></tbody>
    </table>
</div>
<!--end::Table-->
