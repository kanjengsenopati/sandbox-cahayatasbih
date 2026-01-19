@extends('layouts.master', ['title' => 'Data Pendaftaran PPDB'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Pendaftaran PPDB</h1>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <!-- Filter Section -->
            <div class="card mb-7">
                <div class="card-body">
                    <form action="{{ route('registrations.index') }}" method="GET" class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="w-200px">
                            <select name="school_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Sekolah">
                                <option value="">Semua Sekolah</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-200px">
                            <select name="status" class="form-select form-select-solid" data-control="select2" data-placeholder="Status Status">
                                <option value="">Semua Status</option>
                                <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                                <option value="VERIFIED" {{ request('status') == 'VERIFIED' ? 'selected' : '' }}>Verified</option>
                                <option value="ACCEPTED" {{ request('status') == 'ACCEPTED' ? 'selected' : '' }}>Accepted</option>
                                <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('registrations.index') }}" class="btn btn-light">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="card">
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th class="min-w-100px">No. Reg</th>
                                    <th class="min-w-150px">Nama Siswa</th>
                                    <th class="min-w-125px">Sekolah</th>
                                    <th class="min-w-125px">Jalur</th>
                                    <th class="min-w-100px">Status</th>
                                    <th class="min-w-100px">Pembayaran</th>
                                    <th class="text-end min-w-70px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fw-bold text-gray-600">
                                @forelse($registrations as $reg)
                                <tr>
                                    <td>{{ $reg->registration_code }}</td>
                                    <td>{{ $reg->name }}</td>
                                    <td>{{ $reg->track->school->name ?? '-' }}</td>
                                    <td>{{ $reg->registration_type }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($reg->status) {
                                                'DRAFT' => 'badge-light-secondary',
                                                'SUBMITTED' => 'badge-light-warning',
                                                'VERIFIED' => 'badge-light-info',
                                                'ACCEPTED' => 'badge-light-success',
                                                'REJECTED' => 'badge-light-danger',
                                                default => 'badge-light-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $reg->status }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $reg->payment_status === 'PAID' ? 'badge-light-success' : 'badge-light-danger' }}">
                                            {{ $reg->payment_status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('registrations.show', $reg->id) }}" class="btn btn-sm btn-light btn-active-light-primary">Detail</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data pendaftaran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-end">
                        {{ $registrations->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
