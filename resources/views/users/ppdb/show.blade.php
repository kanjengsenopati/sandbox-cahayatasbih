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
                        <a href="{{ route('wali.ppdb.index') }}" class="text-muted text-hover-primary">PPDB</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List PPDB</li>
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
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::About card-->
            <div class="card">
                <!--begin::Body-->
                <div class="card-body p-lg-17">
                    <!--begin::About-->
                    <div class="mb-18">
                        <!--begin::Wrapper-->
                        <div class="mb-10 text-center">
                            <!--begin::Top-->
                            <h3 class="fs-2hx text-dark mb-5">{{ $ppdb->name }}</h3>
                            <div class="fs-5 text-muted fw-bold">
                                Jalur Pendaftaran {{ $ppdb->ppdbType->name }} - {{ $ppdb->academicYear->name }} - {{
                                $ppdb->school->name }}
                            </div>
                            <!--end::Top-->
                        </div>
                        <!--end::Text-->
                        <!--begin::Overlay-->
                        <div class="overlay">
                            <!--begin::Image-->
                            <img class="w-100 card-rounded" src="{{ asset($ppdb->image) }}" alt="" />
                            <!--end::Image-->
                            <!--begin::Links-->
                            {{-- <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                                <a href="{{ route('wali.ppdb.create') }}"
                                    class="btn btn-primary btn-sm btn-active-primary">Daftar</a>
                            </div> --}}
                            <!--end::Links-->
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Description-->
                    <div class="fs-5 fw-bold text-gray-600">
                        <!--begin::Text-->
                        <p class="mb-8">{!! $ppdb->description !!}</p>
                        <!--end::Text-->
                    </div>
                    <!--end::Description-->
                    <!--begin::Daftar Button (Responsive)-->
                    <div class="text-center">
                        <a href="{{ route('wali.ppdb.create') }}"
                            class="btn btn-primary btn-lg btn-active-primary">Daftar Sekarang</a>
                    </div>
                    <!--end::Daftar Button-->
                </div>
                <!--end::About-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Statistics-->
    </div>
    <!--end::Post-->
</div>
@endsection