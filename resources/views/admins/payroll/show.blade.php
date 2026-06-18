@extends('layouts.master', ['title' => 'Detail Slip Gaji'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Detail Slip Gaji</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('payroll.index') }}" class="text-muted text-hover-primary">Payroll</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Detail Slip</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title d-flex flex-column">
                        <h2>Slip Gaji: {{ $slip->presensiable ? $slip->presensiable->name : 'Karyawan' }}</h2>
                        <span class="text-muted fs-7">Periode: {{ $slip->period_start->format('d F Y') }} - {{ $slip->period_end->format('d F Y') }}</span>
                    </div>
                    <div class="card-toolbar">
                        @if($slip->status === 'draft')
                            <form action="{{ route('payroll.approve', $slip->id) }}" method="POST" class="me-2">
                                @csrf
                                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Setujui (Approve)</button>
                            </form>
                        @elseif($slip->status === 'approved')
                            <form action="{{ route('payroll.pay', $slip->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary"><i class="fa fa-credit-card"></i> Tandai Dibayar (Pay)</button>
                            </form>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-8">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%" class="fw-bold">Tipe Pegawai:</td>
                                    <td>{{ $slip->presensiable_type === 'App\Models\Admin' ? 'Admin / Guru / Staff' : 'Petugas / Karyawan' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status Slip Gaji:</td>
                                    <td>
                                        @if($slip->status === 'draft')
                                            <span class="badge badge-warning">Draft (Menunggu Verifikasi)</span>
                                        @elseif($slip->status === 'approved')
                                            <span class="badge badge-primary">Disetujui (Approved)</span>
                                        @else
                                            <span class="badge badge-success">Dibayarkan (Paid)</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($slip->approved_by)
                                <tr>
                                    <td class="fw-bold">Diverifikasi Oleh:</td>
                                    <td>{{ $slip->approver ? $slip->approver->name : 'System' }} pada {{ $slip->approved_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Kolom Pendapatan -->
                        <div class="col-md-6 border-end">
                            <h3 class="border-bottom pb-3 mb-4 text-success"><i class="fa fa-arrow-up text-success"></i> Pendapatan (Earnings)</h3>
                            <table class="table table-borderless">
                                <tr>
                                    <td>Gaji Pokok:</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($slip->base_salary, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Tunjangan Kehadiran ({{ $slip->total_present_days }} Hari):</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($slip->total_attendance_allowance, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Tunjangan Transport ({{ $slip->total_present_days }} Hari):</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($slip->total_transport_allowance, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold">Total Pendapatan:</td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($slip->base_salary + $slip->total_attendance_allowance + $slip->total_transport_allowance, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Kolom Potongan -->
                        <div class="col-md-6">
                            <h3 class="border-bottom pb-3 mb-4 text-danger"><i class="fa fa-arrow-down text-danger"></i> Potongan (Deductions)</h3>
                            <table class="table table-borderless">
                                <tr>
                                    <td>Denda Terlambat ({{ $slip->total_late_minutes }} Menit):</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($slip->total_lateness_penalty, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Denda Mangkir ({{ $slip->total_absent_days }} Hari):</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($slip->total_absence_penalty, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold">Total Potongan:</td>
                                    <td class="text-end fw-bold text-danger">
                                        Rp {{ number_format($slip->total_lateness_penalty + $slip->total_absence_penalty, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Ringkasan Bersih -->
                    <div class="row mt-8 border-top pt-6">
                        <div class="col-md-12 text-end">
                            <h2 class="text-dark">Gaji Bersih Diterima (Net Salary)</h2>
                            <h1 class="text-primary font-bold mt-2" style="font-size: 2.5rem;">
                                Rp {{ number_format($slip->net_salary, 0, ',', '.') }}
                            </h1>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
