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
            <div class="chart">
                <div id="chart-line" class="chart-canvas" height="350px"></div>
            </div>
        </div>
        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::List widget 11-->
            <div class="card card-flush h-xl-100">
                <div class="card-body pt-2">
                    <div class="d-flex flex-stack">
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <img src="{{ asset('assets/media/illustrations/dashboard/dashboard-1.jpg') }}"
                                class="mw-100 mh-200px" alt="">
                        </div>
                    </div>
                </div>
                <!--begin::Header-->
                <div class="card-header pt-7 mb-3">
                    <!--begin::Title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-gray-800">Statistik</span>
                        <span class="text-gray-400 mt-1 fw-bold fs-6" id="date-time"></span>
                    </h3>

                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-4">
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
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Siswa</a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Siswa yang
                                    Aktif</span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block">{{ $data['total_students'] ?? 0 }}</span>
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
                                    <i class="fas fa-chalkboard-teacher fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Kelas</a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Kelas yang
                                    Aktif</span>
                                </span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block">{{ $data['total_classes'] ?? 0 }}</span>
                            <!--end::Number-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-5"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    {{-- <div class="d-flex flex-stack">
                        <!--begin::Section-->
                        <div class="d-flex align-items-center me-5">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label">
                                    <i class="fas fa-download fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total Download
                                </a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Total Download
                                    Etika dan SOP</span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block"></span>
                            <!--end::Number-->
                        </div>
                        <!--end::Wrapper-->
                    </div> --}}
                </div>

                <!--end::Body-->
            </div>
            <!--end::List widget 11-->

        </div>
        <!--end::Col-->
        <!--begin::Col-->

        <!--end::Col-->
    </div>
    <!--end::Row-->
    {{-- <div class="row gy-5 g-xl-10 mt-8 mx-4">
        <div class="col-xl-8">
            <div class="card">

                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-start border-0 pt-6">
                    <!--begin::Card title-->
                    <span class="card-title fw-bolder text-dark fs-3">Pelaporan WBS Terbaru</span>
                </div>
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-user" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                    <th width="3%">No</th>
                                    <th width="20%">Kode Pelaporan</th>
                                    <th width="20%">Kategori</th>
                                    <th>Status</th>
                                    <th>Pelapor</th>
                                    <th width="10%" class="text-center"> Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
            </div>
        </div>
        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::List widget 11-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->

                <div class="card-header pt-7 mb-3">
                    <!--begin::Title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder text-gray-800">Statistik WBS </span>
                        <span class="text-gray-400 mt-1 fw-bold fs-6"></span>
                    </h3>

                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-4">
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">

                        <!--begin::Section-->
                        <div class="d-flex align-items-center me-5">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label">
                                    <i class="fas fa-envelope-open-text fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Diterima</a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan yang
                                    diterima</span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block"></span>
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
                                    <i class="fas fa-user-check fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Verifikasi </a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan yang
                                    diverifikasi
                                </span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block">
                            </span>
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
                                    <i class="fas fa-magnifying-glass-arrow-right fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Investigasi </a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan yang
                                    diinvestigasi</span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block"></span>
                            <!--end::Number-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Item-->

                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-5"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <!--begin::Section-->
                        <div class="d-flex align-items-center me-5">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label">
                                    <i class="fas fa-solid fa-rotate fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Dalam
                                    Proses </a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Pelaporan Dalam
                                    Proses
                                </span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block"></span>
                            <!--end::Number-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Item-->
                    <div class="separator separator-dashed my-5"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <!--begin::Section-->
                        <div class="d-flex align-items-center me-5">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label">
                                    <i class="fas fa-circle-check fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Tindakan Selesai</a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Tindakan Korekftif
                                    Selesai
                                </span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block"></span>
                            <!--end::Number-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Item-->
                    <div class="separator separator-dashed my-5"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <!--begin::Section-->
                        <div class="d-flex align-items-center me-5">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label">
                                    <i class="fas fa-envelope-circle-check fs-1 p-0 text-gray-600"></i>
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Content-->
                            <div class="me-5">
                                <!--begin::Title-->
                                <a href="#" class="text-gray-800 fw-bolder text-hover-primary fs-6">Total
                                    Selesai</a>
                                <!--end::Title-->
                                <!--begin::Desc-->
                                <span class="text-gray-400 fw-bold fs-7 d-block text-start ps-0">Laporan Selesai
                                </span>
                                <!--end::Desc-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Wrapper-->
                        <div class="text-gray-400 fw-bolder fs-7 text-end">
                            <!--begin::Number-->
                            <span class="text-gray-800 fw-bolder fs-6 d-block"></span>
                            <!--end::Number-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                </div>

                <!--end::Body-->
            </div>
            <!--end::List widget 11-->

        </div>
        <!--end::Col-->
        <!--begin::Col-->

        <!--end::Col-->
    </div> --}}
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