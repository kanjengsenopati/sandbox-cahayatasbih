<div class="card">
    <!--begin::Card header-->
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-4 border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <h3 class="text-dark">Arsip Riwayat Pembayaran</h3>
        </div>
        <!--begin::Card toolbar (Filters)-->
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <label class="fs-7 fw-bold text-gray-700 mb-0">Mulai:</label>
                <input type="date" id="archive-start-date" class="form-control form-control-solid form-control-sm" style="width: 150px;">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="fs-7 fw-bold text-gray-700 mb-0">Selesai:</label>
                <input type="date" id="archive-end-date" class="form-control form-control-solid form-control-sm" style="width: 150px;">
            </div>
            <button id="archive-btn-filter" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
            <button id="archive-btn-reset" class="btn btn-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</button>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Table-->
        <div class="table-responsive">
            <table id="table-archive" class="table align-middle table-row-dashed">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Siswa</th>
                        <th>Jumlah Pembayaran</th>
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
    </div>
    <!--end::Card body-->
</div>
