@extends('layouts.master', ['title' => 'Dashboard'])
@push('css')
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 32px 0 rgba(34, 41, 47, 0.2);
    }

    .card-icon {
        font-size: 2.5rem;
        color: #fff;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(1);
        }
    }

    .progress-bar {
        background-color: #6c5ce7;
    }

    .jumbotron {
        background-color: #f8f9fa;
        padding: 3rem 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1);
        animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    .jumbotron-icon {
        font-size: 4rem;
        color: #6c5ce7;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-30px);
        }

        60% {
            transform: translateY(-15px);
        }
    }
</style>
@endpush
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Dashboard</h1>
            </div>
        </div>
    </div>

    <div class="row gy-5 g-xl-10 mt-8 mx-4">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="row gx-3 gy-3">
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100 animate__animated animate__fadeInUp">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-user-group card-icon"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Wali Santri</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $data['total_parents'] ?? 0
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100 animate__animated animate__fadeInUp"
                            data-wow-delay="0.2s">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-users card-icon"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Siswa Aktif</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $data['total_students'] ??
                                    0
                                    }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100 animate__animated animate__fadeInUp"
                            data-wow-delay="0.4s">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-users card-icon"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Santri Aktif</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $totalSantriAktif ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 py-2">
                        <div class="card bg-primary text-white h-100 animate__animated animate__fadeInUp"
                            data-wow-delay="0.6s">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <i class="fas fa-users card-icon"></i>&nbsp;
                                    <h5 class="card-title text-center text-white">Total Santri Keluar</h5>
                                </div>
                                <br>
                                <p class="card-text text-center" style="font-size: 20px;">{{ $totalSantriKeluar ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="jumbotron animate__animated animate__fadeInUp">
        <div class="d-flex justify-content-center">
            <i class="fas fa-chart-line jumbotron-icon"></i>
        </div>
        <h1 class="display-4 text-center">Selamat Datang di Dashboard</h1>
        <p class="lead text-center">Selamat Datang, {{ Auth::user()->name }}</p>
    </div>
</div>
@endsection