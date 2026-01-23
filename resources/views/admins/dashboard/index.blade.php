@extends('layouts.master', ['title' => 'Dashboard Utama'])

@push('css')
<style>
    .card-metric {
        transition: transform 0.2s;
    }
    .card-metric:hover {
        transform: translateY(-5px);
    }
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-soft-purple {
        background-color: rgba(111, 66, 193, 0.1);
    }
    .text-purple {
        color: #6f42c1 !important;
    }
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    .progress-bar-purple {
        background-color: #6f42c1;
    }
</style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                    Dashboard Utama
                </h1>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            
            <!-- Row 1: Key Metrics -->
            <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
                <!-- Total Santri -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-4 border-0 card-metric">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-circle bg-soft-purple me-4">
                                <i class="fas fa-user-graduate fs-2 text-purple"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bolder fs-2x text-dark">{{ number_format($totalSantri) }}</span>
                                <span class="fw-bold text-gray-400">Santri Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Guru & Staff -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-4 border-0 card-metric">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-circle bg-light-primary me-4">
                                <i class="fas fa-chalkboard-teacher fs-2 text-primary"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bolder fs-2x text-dark">{{ number_format($totalStaff) }}</span>
                                <span class="fw-bold text-gray-400">Guru & Staff</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Wali Santri -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-4 border-0 card-metric">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-circle bg-light-success me-4">
                                <i class="fas fa-users fs-2 text-success"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bolder fs-2x text-dark">{{ number_format($totalWali) }}</span>
                                <span class="fw-bold text-gray-400">Wali Santri</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Kelas -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-xl-stretch mb-xl-8 shadow-sm rounded-4 border-0 card-metric">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-circle bg-light-warning me-4">
                                <i class="fas fa-school fs-2 text-warning"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bolder fs-2x text-dark">{{ number_format($totalKelas) }}</span>
                                <span class="fw-bold text-gray-400">Total Kelas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Deep Dive Data -->
            <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
                <!-- Sebaran Santri -->
                <div class="col-lg-8">
                    <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm rounded-4 border-0">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">Populasi Santri per Jenjang</span>
                                <span class="text-muted mt-1 fw-bold fs-7">Rincian sebaran siswa per unit sekolah</span>
                            </h3>
                        </div>
                        <div class="card-body py-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bolder text-muted bg-light">
                                            <th class="ps-4 min-w-150px rounded-start">Unit Sekolah</th>
                                            <th class="min-w-100px">Laki-laki</th>
                                            <th class="min-w-100px">Perempuan</th>
                                            <th class="min-w-100px">Total Santri</th>
                                            <th class="min-w-100px rounded-end">Jumlah Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schoolData as $school)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-45px me-5">
                                                        <span class="symbol-label bg-light-purple text-purple fw-bold">
                                                            {{ substr($school->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-dark fw-bolder text-hover-primary fs-6">{{ $school->name }}</span>
                                                        <span class="text-muted fw-bold text-muted d-block fs-7">{{ $school->type }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-primary fs-7 fw-bold">{{ $school->count_l }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-danger fs-7 fw-bold">{{ $school->count_p }}</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-bolder fs-6">{{ $school->total_students }}</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-bolder fs-6">{{ $school->total_classes }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gender Ratio Chart -->
                <div class="col-lg-4">
                    <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm rounded-4 border-0">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">Komposisi Putra vs Putri</span>
                                <span class="text-muted mt-1 fw-bold fs-7">Rasio Gender Global</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            <div class="d-flex flex-center position-relative h-250px">
                                <canvas id="genderChart"></canvas>
                            </div>
                            <div class="d-flex flex-stack mt-4">
                                <div class="d-flex align-items-center me-3">
                                    <span class="bullet bullet-vertical h-35px bg-primary w-5px me-3"></span>
                                    <div class="d-flex flex-column text-start">
                                        <span class="fw-bold text-gray-600 fs-7">Putra</span>
                                        <span class="fw-bolder text-gray-800 fs-5">{{ $genderRatio['l'] }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center me-3">
                                    <span class="bullet bullet-vertical h-35px bg-danger w-5px me-3"></span>
                                    <div class="d-flex flex-column text-start">
                                        <span class="fw-bold text-gray-600 fs-7">Putri</span>
                                        <span class="fw-bolder text-gray-800 fs-5">{{ $genderRatio['p'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Activity & Actions -->
            <div class="row g-5 g-xl-8">
                <!-- Login Activity -->
                <div class="col-lg-6">
                    <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm rounded-4 border-0">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">Pengguna Aktif Hari Ini</span>
                                <span class="text-muted mt-1 fw-bold fs-7">Monitoring Login Harian</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Staff Login -->
                            <div class="d-flex flex-column mb-7">
                                <div class="d-flex justify-content-between w-100 fs-4 fw-bolder mb-3">
                                    <span>Staff Login</span>
                                    <span>{{ $loginActivity['staff_count'] }} / {{ $loginActivity['staff_total'] }}</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-light-purple rounded">
                                    <div class="bg-purple rounded h-8px" role="progressbar" style="width: {{ $loginActivity['staff_percentage'] }}%;" aria-valuenow="{{ $loginActivity['staff_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="fw-bold text-gray-400 mt-2 ms-3">{{ $loginActivity['staff_percentage'] }}% Staff telah login hari ini</div>
                            </div>

                            <!-- Wali Santri Login -->
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between w-100 fs-4 fw-bolder mb-3">
                                    <span>Wali Santri Login</span>
                                    <span>{{ $loginActivity['wali_count'] }} / {{ $loginActivity['wali_total'] }}</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-light-success rounded">
                                    <div class="bg-success rounded h-8px" role="progressbar" style="width: {{ $loginActivity['wali_percentage'] }}%;" aria-valuenow="{{ $loginActivity['wali_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="fw-bold text-gray-400 mt-2 ms-3">{{ $loginActivity['wali_percentage'] }}% Wali Santri telah akses aplikasi</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <!-- Agenda Widget -->
                <div class="col-lg-6">
                    <div class="card card-xl-stretch mb-5 mb-xl-8 shadow-sm rounded-4 border-0">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">Agenda & Kegiatan</span>
                                <span class="text-muted mt-1 fw-bold fs-7">Jadwal kegiatan mendatang</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            @if(isset($upcomingSchedules) && $upcomingSchedules->count() > 0)
                                <div class="timeline-label">
                                    @foreach($upcomingSchedules as $schedule)
                                        <div class="d-flex align-items-center mb-6">
                                            <!-- Date Box -->
                                            <div class="d-flex flex-column align-items-center justify-content-center bg-light-purple rounded min-w-60px h-60px me-5">
                                                <span class="fs-2 fw-bolder text-purple lh-1">{{ $schedule->date->format('d') }}</span>
                                                <span class="fs-7 fw-bold text-gray-500 lh-1 mt-1">{{ \Illuminate\Support\Str::upper($schedule->date->translatedFormat('M')) }}</span>
                                            </div>
                                            
                                            <!-- Content -->
                                            <div class="d-flex flex-column flex-grow-1">
                                                <span class="text-dark fw-bolder fs-6 mb-1">{{ $schedule->name }}</span>
                                                <span class="text-gray-400 fw-bold fs-7 description-truncate mb-1">{{ \Illuminate\Support\Str::limit($schedule->description, 50) }}</span>
                                                <div>
                                                    @if($schedule->type == 'SCHOOL' && $schedule->school)
                                                        <span class="badge badge-light-info fs-8 fw-bolder">{{ $schedule->school->name }}</span>
                                                    @else
                                                        <span class="badge badge-light-warning fs-8 fw-bolder">Umum/Pondok</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                                     <i class="fas fa-calendar-times fs-3x text-gray-300 mb-4"></i>
                                     <span class="text-gray-400 fw-bold">Belum ada agenda mendatang</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('genderChart').getContext('2d');
        var genderChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Putra', 'Putri'],
                datasets: [{
                    data: [{{ $genderRatio['l'] }}, {{ $genderRatio['p'] }}],
                    backgroundColor: [
                        '#009ef7', // Blue for Boys
                        '#f1416c'  // Pink for Girls
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                family: 'Inter'
                            }
                        }
                    }
                },
                cutout: '70%',
            }
        });
    });
</script>
@endpush