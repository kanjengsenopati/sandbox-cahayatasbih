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
            <label class="fs-7 fw-bold text-gray-700 mb-0">Lembaga:</label>
            <select id="archive-school-id" class="form-select form-select-solid form-select-sm" style="width: 140px;">
                <option value="">Semua</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="fs-7 fw-bold text-gray-700 mb-0">Kelas:</label>
            <select id="archive-classroom-id" class="form-select form-select-solid form-select-sm" style="width: 140px;">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" data-school="{{ $cls->school_id }}">{{ $cls->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="fs-7 fw-bold text-gray-700 mb-0">Cari:</label>
            <input type="text" id="archive-search-name" class="form-control form-control-solid form-control-sm" placeholder="Nama / NIS..." style="width: 150px;">
        </div>
        <!--begin::Custom Date Range Container-->
        <div class="d-flex align-items-center gap-2" id="archive-custom-date-container">
            <div class="d-flex align-items-center gap-1">
                <label class="fs-7 fw-bold text-gray-700 mb-0">Mulai:</label>
                <input type="date" id="archive-start-date" class="form-control form-control-solid form-control-sm" value="{{ now()->subDays(6)->format('Y-m-d') }}" style="width: 135px;">
            </div>
            <div class="d-flex align-items-center gap-1">
                <label class="fs-7 fw-bold text-gray-700 mb-0">Selesai:</label>
                <input type="date" id="archive-end-date" class="form-control form-control-solid form-control-sm" value="{{ now()->format('Y-m-d') }}" style="width: 135px;">
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
    <table id="table-archive" class="table align-middle table-row-dashed fs-7 gy-3" style="width: 100%;">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 4%">No</th>
                <th style="width: 18%">Siswa</th>
                <th style="width: 12%">Nominal</th>
                <th style="width: 8%">Kode Unik</th>
                <th style="width: 18%">Bank Tujuan</th>
                <th style="width: 10%">Bukti Transfer</th>
                <th style="width: 12%">Status</th>
                <th style="width: 10%">Petugas</th>
                <th style="width: 10%">Tanggal & Waktu</th>
                <th class="text-center min-w-80px" style="width: 8%">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-bold"></tbody>
    </table>
</div>
<!--end::Table-->
