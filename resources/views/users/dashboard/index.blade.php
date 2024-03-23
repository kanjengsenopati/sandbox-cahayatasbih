@extends('layouts.master', ['title' => 'Dashboard'])
@push('css')
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('content')
<!--begin::Container-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Dashboard </h1>
                <!--end::Title-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <!--end::Row-->
    <!--begin::Row-->
    <div class="row gy-5 g-xl-10 mt-8 mx-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-gray-800">Informasi</span>
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Add your content here -->
                    @foreach ($informations as $information)
                    <div class="row mb-4">
                        <!-- Image on the left side -->
                        <div class="col-md-4">
                            <img src="{{ asset(@$information->image) }}" class="img-fluid" alt="Image">
                        </div>
                        <!-- Information on the right side -->
                        <div class="col-md-8">
                            <!-- Card for the information -->
                            <div class="card">
                                <div class="card-body">
                                    <!-- Category -->
                                    <p class="text-muted">Kategori: {{ $information->informationCategory->name ?? '' }}
                                    </p>
                                    <div class="d-flex justify-content-center">
                                        <h5 class="card-title text-start">{{ $information->title ?? '' }}</h5>
                                    </div>
                                    <p class="card-text text-center" style="font-size: 24px;">{!!
                                        substr($information->content, 0, 100) !!}...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- "Lihat Semua" link -->
                    <div class="separator separator-dashed my-5"></div>
                    <div class="text-center">
                        <a href="#">Lihat Semua</a>
                    </div>
                </div>
            </div>
        </div>
        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::List widget 11-->
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <!--begin::Title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-gray-800">Informasi Santri</span>
                        <span class="text-gray-400 mt-1 fw-bold fs-6" id="date-time"></span>
                    </h3>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                @foreach ($students as $student)
                <!-- Begin: Per card for each student -->
                <div class="card mb-4">
                    <div class="card-body">
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-user fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">{{
                                        $student->name ??
                                        '' }}</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">{{ $student->nis ??
                                        '' }}</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span class="text-gray-800 fw-bolder fs-6 d-block">{{ $student->classroom->name ??
                                    '' }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-coins fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Saldo</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Saldo Santri</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span class="text-gray-800 fw-bolder fs-6 d-block">Rp {{ number_format($student->saldo,
                                    0, ',', '.') }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-5"></div>
                        <!--end::Separator-->
                        <!--begin::Item-->
                        <div class="d-flex flex-stack">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center me-5">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label">
                                        <i class="fas fa-piggy-bank fs-1 p-0 text-gray-600"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="me-5">
                                    <!--begin::Title-->
                                    <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Tabungan</a>
                                    <!--end::Title-->
                                    <!--begin::Desc-->
                                    <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Tabungan
                                        Santri</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Wrapper-->
                            <div class="text-gray-400 fw-bolder fs-7 text-end">
                                <!--begin::Number-->
                                <span class="text-gray-800 fw-bolder fs-6 d-block">Rp {{ number_format($student->saving,
                                    0, ',', '.') }}</span>
                                <!--end::Number-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Item-->
                    </div>
                </div>
                {{-- add line --}}
                <div class="separator separator-dashed my-5"></div>
                <!-- End: Per card for each student -->
                @endforeach
                <!--end::Body-->
            </div>
            <!--end::List widget 11-->
        </div>
        <!--end::Col-->
        <!--begin::Col-->

        <!--end::Col-->
    </div>
</div>
<!--end::Container-->
@endsection
@push('js')
<script>
    // add date and time in #date-time
        function dateTime() {
            var date = new Date();
            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var seconds = date.getSeconds();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var strTime = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            var fullDate = day + '/' + month + '/' + year + ' ' + strTime;
            document.getElementById('date-time').innerHTML = fullDate;
        }
        setInterval(dateTime, 1000);
</script>
@endpush