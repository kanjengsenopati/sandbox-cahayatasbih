@extends('layouts.user', ['title' => 'PPDB'])

@section('content')
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">PPDB</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('wali.ppdb-history.index') }}" class="text-muted text-hover-primary">Riwayat
                            PPDB</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Detail Riwayat PPDB</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body p-0">
                    <!--begin::Wrapper-->
                    @if ($ppdbHistory->status == 'PENDING')
                    <div class="card-px text-center py-5 my-10">
                        <div class="text-center px-2">
                            <img class="mw-100 mh-300px" alt="Pay Now"
                                src="{{ asset('assets/media/illustrations/pay_now.jpg') }}" />
                        </div>
                        <!--begin::Title-->
                        <h2 class="fs-2x fw-bolder mb-10">Proses Pembayaran Sekarang</h2>
                        <!--end::Title-->
                        <!--begin::Description-->
                        <p class="text-gray-400 fs-4 fw-bold mb-10">Pendaftaran PPDB anda telah selesai, silahkan
                            <br />melakukan pembayaran sekarang untuk melanjutkan ke tahap selanjutnya.
                            <br />Batas Pembayaran : {{
                            $ppdbHistory->transactionDetails->first()->transaction?->created_at->addDays(1)->format('d F
                            Y H:i') }} WIB
                        </p>
                        <!--end::Description-->
                        <!--begin::Action-->
                        <a href="{{ $ppdbHistory->transactionDetails->first()->transaction?->payment_link ?? '' }}"
                            target="_blank" class="btn btn-primary">Bayar Sekarang</a>
                        <!--end::Action-->
                    </div>
                    @elseif ($ppdbHistory->status == 'PAID')
                    <div class="card-px text-center py-20 my-10">
                        <div class="text-center px-2">
                            <img class="mw-100 mh-300px" alt="Pay Now"
                                src="{{ asset('assets/media/illustrations/pay_now.jpg') }}" />
                        </div>
                        <!--begin::Title-->
                        <h2 class="fs-2x fw-bolder mb-10">Pembayaran Berhasil</h2>
                        <!--end::Title-->
                        <!--begin::Description-->
                        <p class="text-gray-400 fs-4 fw-bold mb-10">Pembayaran PPDB anda telah berhasil, silahkan
                            <br />menunggu konfirmasi dari pihak sekolah.

                        </p>
                        <!--end::Description-->
                    </div>
                    @elseif ($ppdbHistory->status == 'REJECTED')
                    <div class="card-px text-center py-5 my-10">
                        <div class="text-center px-2">
                            <img class="mw-100 mh-300px" alt="Pay Now"
                                src="{{ asset('assets/media/svg/approval/rejected.jpg') }}" />
                        </div>
                        <!--begin::Title-->
                        <h2 class="fs-2x fw-bolder mb-5">
                            <i class="bi bi-x-circle-fill text-danger me-2"></i>
                            Maaf, Anda Tidak Lolos Seleksi Administrasi
                        </h2>
                        <!--end::Title-->
                        <!--begin::Description-->
                        <p class="text-gray-400 fs-4 fw-bold mb-10">Keterangan : {{ $ppdbHistory->note ?? '' }}
                        </p>
                        <!--end::Description-->
                    </div>
                    @elseif($ppdbHistory->status == 'APPROVED')
                    <div class="card-px text-center py-5 my-10">
                        <div class="text-center px-2">
                            <img class="mw-100 mh-300px" alt="Success"
                                src="{{ asset('assets/media/svg/approval/approved.svg') }}" />
                        </div>
                        <!--begin::Title-->
                        <h2 class="fs-2x fw-bolder mb-5">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Selamat, Anda Lolos Seleksi Administrasi
                        </h2>
                        <!--end::Title-->
                        <!--begin::Description-->
                        <p class="text-gray-400 fs-4 fw-bold mb-10">Keterangan : {{ $ppdbHistory->note ?? '' }}
                        </p>
                        <!--end::Description-->
                    </div>
                    @endif
                    <!--end::Wrapper-->
                    <!--begin::Illustration-->
                    {{-- <div class="text-center px-4">
                        <img class="mw-100 mh-300px" alt="Pay Now"
                            src="{{ asset('assets/media/illustrations/pay_now.jpg') }}" />
                    </div> --}}
                    <!--end::Illustration-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection