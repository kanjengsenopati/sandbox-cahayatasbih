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
            <!--begin::Actions-->

            <!--end::Actions-->
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
                        <div class="mb-10">
                            <!--begin::Top-->
                            <div class="text-center mb-15">
                                <!--begin::Title-->
                                <h3 class="fs-2hx text-dark mb-5">{{ $ppdb->name }}</h3>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <div class="fs-5 text-muted fw-bold">Jalur Pendaftaran {{ $ppdb->ppdbType->name }} - {{
                                    $ppdb->academicYear->name }} - {{ $ppdb->school->name }}</div>
                            </div>
                            <!--end::Text-->
                        </div>
                        <!--end::Top-->
                        <!--begin::Overlay-->
                        <div class="overlay">
                            <!--begin::Image-->
                            <img class="w-100 card-rounded" src="{{ asset($ppdb->image) }}" alt="" />
                            <!--end::Image-->
                            <!--begin::Links-->
                            <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                                <a href="{{ route('wali.ppdb.create') }}"
                                    class="btn btn-primary btn-sm btn-active-primary">Daftar</a>
                            </div>
                            <!--end::Links-->
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Description-->
                    <div class="fs-5 fw-bold text-gray-600">

                        <!--begin::Text-->
                        <p class="mb-8">{!! $ppdb->description !!}</p>
                        </p>
                        <!--end::Text-->
                        <!--begin::Text-->
                    </div>
                    <!--end::Description-->
                </div>
                <!--end::About-->
                <!--begin::Statistics-->
                <div class="card bg-light mb-18">
                    <!--begin::Body-->
                    <div class="card-body py-15">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-center">
                            <!--begin::Items-->
                            <div class="d-flex justify-content-between mb-10 mx-auto w-xl-900px">
                                <!--begin::Item-->
                                <div class="octagon d-flex flex-center h-200px w-200px bg-body mx-2">
                                    <!--begin::Content-->
                                    <div class="text-center">
                                        <!--begin::Symbol-->
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                                        <span class="svg-icon svg-icon-2tx svg-icon-primary">
                                            {{-- svg kouta --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M13 10.9128V3.01281C13 2.41281 13.5 1.91281 14.1 2.01281C16.1 2.21281 17.9 3.11284 19.3 4.61284C20.7 6.01284 21.6 7.91285 21.9 9.81285C22 10.4129 21.5 10.9128 20.9 10.9128H13Z"
                                                    fill="currentColor" />
                                                <path opacity="0.3"
                                                    d="M13 12.9128V20.8129C13 21.4129 13.5 21.9129 14.1 21.8129C16.1 21.6129 17.9 20.7128 19.3 19.2128C20.7 17.8128 21.6 15.9128 21.9 14.0128C22 13.4128 21.5 12.9128 20.9 12.9128H13Z"
                                                    fill="currentColor" />
                                                <path opacity="0.3"
                                                    d="M11 19.8129C11 20.4129 10.5 20.9129 9.89999 20.8129C5.49999 20.2129 2 16.5128 2 11.9128C2 7.31283 5.39999 3.51281 9.89999 3.01281C10.5 2.91281 11 3.41281 11 4.01281V19.8129Z"
                                                    fill="currentColor" />

                                        </span>
                                        <!--end::Svg Icon-->
                                        <!--end::Symbol-->
                                        <!--begin::Text-->
                                        <div class="mt-1">
                                            <!--begin::Animation-->
                                            <div
                                                class="fs-lg-2hx fs-2x fw-bolder text-gray-800 d-flex align-items-center">
                                                <div class="min-w-70px" data-kt-countup="true"
                                                    data-kt-countup-value="{{ $ppdb->capacity }}">{{ $ppdb->capacity
                                                    }}</div>
                                            </div>
                                        </div>
                                        <!--end::Animation-->
                                        <!--begin::Label-->
                                        <span class="text-gray-600 fw-bold fs-5 lh-0">Kouta</span>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Text-->
                                </div>
                                <div class="octagon d-flex flex-center h-200px w-200px bg-body mx-2">
                                    <!--begin::Content-->
                                    <div class="text-center">
                                        <!--begin::Symbol-->
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                                        <span class="svg-icon svg-icon-2tx svg-icon-primary">
                                            {{-- svg kouta --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M13 10.9128V3.01281C13 2.41281 13.5 1.91281 14.1 2.01281C16.1 2.21281 17.9 3.11284 19.3 4.61284C20.7 6.01284 21.6 7.91285 21.9 9.81285C22 10.4129 21.5 10.9128 20.9 10.9128H13Z"
                                                    fill="currentColor" />
                                                <path opacity="0.3"
                                                    d="M13 12.9128V20.8129C13 21.4129 13.5 21.9129 14.1 21.8129C16.1 21.6129 17.9 20.7128 19.3 19.2128C20.7 17.8128 21.6 15.9128 21.9 14.0128C22 13.4128 21.5 12.9128 20.9 12.9128H13Z"
                                                    fill="currentColor" />
                                                <path opacity="0.3"
                                                    d="M11 19.8129C11 20.4129 10.5 20.9129 9.89999 20.8129C5.49999 20.2129 2 16.5128 2 11.9128C2 7.31283 5.39999 3.51281 9.89999 3.01281C10.5 2.91281 11 3.41281 11 4.01281V19.8129Z"
                                                    fill="currentColor" />

                                        </span>
                                        <!--end::Svg Icon-->
                                        <!--end::Symbol-->
                                        <!--begin::Text-->
                                        <div class="mt-1">
                                            <!--begin::Animation-->
                                            <div
                                                class="fs-lg-2hx fs-2x fw-bolder text-gray-800 d-flex align-items-center">
                                                <div class="min-w-70px" data-kt-countup="true"
                                                    data-kt-countup-value="{{ $ppdb->capacity }}">{{ $ppdb->capacity
                                                    }}</div>
                                            </div>
                                        </div>
                                        <!--end::Animation-->
                                        <!--begin::Label-->
                                        <span class="text-gray-600 fw-bold fs-5 lh-0">Kouta</span>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Text-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Item-->

                        </div>
                        <!--end::Items-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Testimonial-->
                    <div class="fs-2 fw-bold text-muted text-center mb-3">
                        <span class="fs-1 lh-1 text-gray-700">“</span>When you care about your topic, you’ll
                        write about
                        it in a
                        <br />
                        <span class="text-gray-700 me-1">more powerful</span>, emotionally expressive way
                        <span class="fs-1 lh-1 text-gray-700">“</span>
                    </div>
                    <!--end::Testimonial-->
                    <!--begin::Author-->
                    <div class="fs-2 fw-bold text-muted text-center">
                        <a href="../../demo1/dist/account/security.html" class="link-primary fs-4 fw-bolder">Marcus
                            Levy</a>
                        <span class="fs-4 fw-bolder text-gray-600">,KeenThemes CEO</span>
                    </div>
                    <!--end::Author-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics-->
            <!--begin::Section-->
            {{-- <div class="mb-16">
                <!--begin::Top-->
                <div class="text-center mb-12">
                    <!--begin::Title-->
                    <h3 class="fs-2hx text-dark mb-5">Publications</h3>
                    <!--end::Title-->
                    <!--begin::Text-->
                    <div class="fs-5 text-muted fw-bold">Our goal is to provide a complete and robust theme
                        solution
                        <br />to boost all of our customer’s project deployments
                    </div>
                    <!--end::Text-->
                </div>
                <!--end::Top-->
                <!--begin::Row-->
                <div class="row g-10">
                    <!--begin::Col-->
                    <div class="col-md-4">
                        <!--begin::Publications post-->
                        <div class="card-xl-stretch me-md-6">
                            <!--begin::Overlay-->
                            <a class="d-block overlay mb-4" data-fslightbox="lightbox-hot-sales"
                                href="assets/media/stock/600x400/img-73.jpg">
                                <!--begin::Image-->
                                <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-175px"
                                    style="background-image:url('assets/media/stock/600x400/img-73.jpg')"></div>
                                <!--end::Image-->
                                <!--begin::Action-->
                                <div class="overlay-layer bg-dark card-rounded bg-opacity-25">
                                    <i class="bi bi-eye-fill fs-2x text-white"></i>
                                </div>
                                <!--end::Action-->
                            </a>
                            <!--end::Overlay-->
                            <!--begin::Body-->
                            <div class="m-0">
                                <!--begin::Title-->
                                <a href="../../demo1/dist/pages/user-profile/overview.html"
                                    class="fs-4 text-dark fw-bolder text-hover-primary text-dark lh-base">Admin
                                    Panel -
                                    How To Started the Dashboard Tutorial</a>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <div class="fw-bold fs-5 text-gray-600 text-dark mt-3 mb-5">We’ve been focused
                                    on making
                                    a the from also not been afraid to and step away been focused create eye
                                </div>
                                <!--end::Text-->
                                <!--begin::Content-->
                                <div class="fs-6 fw-bolder">
                                    <!--begin::Author-->
                                    <a href="../../demo1/dist/apps/projects/users.html"
                                        class="text-gray-700 text-hover-primary">Jane Miller</a>
                                    <!--end::Author-->
                                    <!--begin::Date-->
                                    <span class="text-muted">on Mar 21 2021</span>
                                    <!--end::Date-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Publications post-->
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="col-md-4">
                        <!--begin::Publications post-->
                        <div class="card-xl-stretch mx-md-3">
                            <!--begin::Overlay-->
                            <a class="d-block overlay mb-4" data-fslightbox="lightbox-hot-sales"
                                href="assets/media/stock/600x400/img-74.jpg">
                                <!--begin::Image-->
                                <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-175px"
                                    style="background-image:url('assets/media/stock/600x400/img-74.jpg')"></div>
                                <!--end::Image-->
                                <!--begin::Action-->
                                <div class="overlay-layer bg-dark card-rounded bg-opacity-25">
                                    <i class="bi bi-eye-fill fs-2x text-white"></i>
                                </div>
                                <!--end::Action-->
                            </a>
                            <!--end::Overlay-->
                            <!--begin::Body-->
                            <div class="m-0">
                                <!--begin::Title-->
                                <a href="../../demo1/dist/pages/user-profile/overview.html"
                                    class="fs-4 text-dark fw-bolder text-hover-primary text-dark lh-base">Admin
                                    Panel -
                                    How To Started the Dashboard Tutorial</a>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <div class="fw-bold fs-5 text-gray-600 text-dark mt-3 mb-5">We’ve been focused
                                    on making
                                    the from v4 to v5 but we have also not been afraid to step away been focused
                                </div>
                                <!--end::Text-->
                                <!--begin::Content-->
                                <div class="fs-6 fw-bolder">
                                    <!--begin::Author-->
                                    <a href="../../demo1/dist/apps/projects/users.html"
                                        class="text-gray-700 text-hover-primary">Cris Morgan</a>
                                    <!--end::Author-->
                                    <!--begin::Date-->
                                    <span class="text-muted">on Apr 14 2021</span>
                                    <!--end::Date-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Publications post-->
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="col-md-4">
                        <!--begin::Publications post-->
                        <div class="card-xl-stretch ms-md-6">
                            <!--begin::Overlay-->
                            <a class="d-block overlay mb-4" data-fslightbox="lightbox-hot-sales"
                                href="assets/media/stock/600x400/img-47.jpg">
                                <!--begin::Image-->
                                <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-175px"
                                    style="background-image:url('assets/media/stock/600x400/img-47.jpg')"></div>
                                <!--end::Image-->
                                <!--begin::Action-->
                                <div class="overlay-layer bg-dark card-rounded bg-opacity-25">
                                    <i class="bi bi-eye-fill fs-2x text-white"></i>
                                </div>
                                <!--end::Action-->
                            </a>
                            <!--end::Overlay-->
                            <!--begin::Body-->
                            <div class="m-0">
                                <!--begin::Title-->
                                <a href="../../demo1/dist/pages/user-profile/overview.html"
                                    class="fs-4 text-dark fw-bolder text-hover-primary text-dark lh-base">Admin
                                    Panel -
                                    How To Started the Dashboard Tutorial</a>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <div class="fw-bold fs-5 text-gray-600 text-dark mt-3 mb-5">We’ve been focused
                                    on making
                                    the from v4 to v5 but we’ve also not been afraid to step away been focused
                                </div>
                                <!--end::Text-->
                                <!--begin::Content-->
                                <div class="fs-6 fw-bolder">
                                    <!--begin::Author-->
                                    <a href="../../demo1/dist/apps/projects/users.html"
                                        class="text-gray-700 text-hover-primary">Carles Nilson</a>
                                    <!--end::Author-->
                                    <!--begin::Date-->
                                    <span class="text-muted">on May 14 2021</span>
                                    <!--end::Date-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Publications post-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->
            </div> --}}
            <!--end::Section-->
            <!--begin::Team-->
            {{-- <div class="mb-18">
                <!--begin::Heading-->
                <div class="text-center mb-12">
                    <!--begin::Title-->
                    <h3 class="fs-2hx text-dark mb-5">Our Great Team</h3>
                    <!--end::Title-->
                    <!--begin::Sub-title-->
                    <div class="fs-5 text-muted fw-bold">It’s no doubt that when a development takes longer to
                        complete,
                        additional costs to
                        <br />integrate and test each extra feature creeps up and haunts most of us.
                    </div>
                    <!--end::Sub-title=-->
                </div>
                <!--end::Heading-->
                <!--begin::Slider-->
                <div class="tns tns-default mb-10">
                    <!--begin::Wrapper-->
                    <div data-tns="true" data-tns-loop="true" data-tns-swipe-angle="false" data-tns-speed="2000"
                        data-tns-autoplay="true" data-tns-autoplay-timeout="18000" data-tns-controls="true"
                        data-tns-nav="false" data-tns-items="1" data-tns-center="false" data-tns-dots="false"
                        data-tns-prev-button="#kt_team_slider_prev" data-tns-next-button="#kt_team_slider_next"
                        data-tns-responsive="{1200: {items: 3}, 992: {items: 2}}">
                        <!--begin::Item-->
                        <div class="text-center">
                            <!--begin::Photo-->
                            <div class="octagon mx-auto mb-5 d-flex w-200px h-200px bgi-no-repeat bgi-size-contain bgi-position-center"
                                style="background-image:url('assets/media/avatars/300-1.jpg')"></div>
                            <!--end::Photo-->
                            <!--begin::Person-->
                            <div class="mb-0">
                                <!--begin::Name-->
                                <a href="#" class="text-dark fw-bolder text-hover-primary fs-3">Paul Miles</a>
                                <!--end::Name-->
                                <!--begin::Position-->
                                <div class="text-muted fs-6 fw-bold mt-1">Development Lead</div>
                                <!--begin::Position-->
                            </div>
                            <!--end::Person-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="text-center">
                            <!--begin::Photo-->
                            <div class="octagon mx-auto mb-5 d-flex w-200px h-200px bgi-no-repeat bgi-size-contain bgi-position-center"
                                style="background-image:url('assets/media/avatars/300-2.jpg')"></div>
                            <!--end::Photo-->
                            <!--begin::Person-->
                            <div class="mb-0">
                                <!--begin::Name-->
                                <a href="#" class="text-dark fw-bolder text-hover-primary fs-3">Melisa
                                    Marcus</a>
                                <!--end::Name-->
                                <!--begin::Position-->
                                <div class="text-muted fs-6 fw-bold mt-1">Creative Director</div>
                                <!--begin::Position-->
                            </div>
                            <!--end::Person-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="text-center">
                            <!--begin::Photo-->
                            <div class="octagon mx-auto mb-5 d-flex w-200px h-200px bgi-no-repeat bgi-size-contain bgi-position-center"
                                style="background-image:url('assets/media/avatars/300-5.jpg')"></div>
                            <!--end::Photo-->
                            <!--begin::Person-->
                            <div class="mb-0">
                                <!--begin::Name-->
                                <a href="#" class="text-dark fw-bolder text-hover-primary fs-3">David Nilson</a>
                                <!--end::Name-->
                                <!--begin::Position-->
                                <div class="text-muted fs-6 fw-bold mt-1">Python Expert</div>
                                <!--begin::Position-->
                            </div>
                            <!--end::Person-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="text-center">
                            <!--begin::Photo-->
                            <div class="octagon mx-auto mb-5 d-flex w-200px h-200px bgi-no-repeat bgi-size-contain bgi-position-center"
                                style="background-image:url('assets/media/avatars/300-20.jpg')"></div>
                            <!--end::Photo-->
                            <!--begin::Person-->
                            <div class="mb-0">
                                <!--begin::Name-->
                                <a href="#" class="text-dark fw-bolder text-hover-primary fs-3">Anne Clarc</a>
                                <!--end::Name-->
                                <!--begin::Position-->
                                <div class="text-muted fs-6 fw-bold mt-1">Project Manager</div>
                                <!--begin::Position-->
                            </div>
                            <!--end::Person-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="text-center">
                            <!--begin::Photo-->
                            <div class="octagon mx-auto mb-5 d-flex w-200px h-200px bgi-no-repeat bgi-size-contain bgi-position-center"
                                style="background-image:url('assets/media/avatars/300-23.jpg')"></div>
                            <!--end::Photo-->
                            <!--begin::Person-->
                            <div class="mb-0">
                                <!--begin::Name-->
                                <a href="#" class="text-dark fw-bolder text-hover-primary fs-3">Ricky Hunt</a>
                                <!--end::Name-->
                                <!--begin::Position-->
                                <div class="text-muted fs-6 fw-bold mt-1">Art Director</div>
                                <!--begin::Position-->
                            </div>
                            <!--end::Person-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="text-center">
                            <!--begin::Photo-->
                            <div class="octagon mx-auto mb-5 d-flex w-200px h-200px bgi-no-repeat bgi-size-contain bgi-position-center"
                                style="background-image:url('assets/media/avatars/300-12.jpg')"></div>
                            <!--end::Photo-->
                            <!--begin::Person-->
                            <div class="mb-0">
                                <!--begin::Name-->
                                <a href="#" class="text-dark fw-bolder text-hover-primary fs-3">Alice Wayde</a>
                                <!--end::Name-->
                                <!--begin::Position-->
                                <div class="text-muted fs-6 fw-bold mt-1">Marketing Manager</div>
                                <!--begin::Position-->
                            </div>
                            <!--end::Person-->
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="text-center">
                            <!--begin::Photo-->
                            <div class="octagon mx-auto mb-5 d-flex w-200px h-200px bgi-no-repeat bgi-size-contain bgi-position-center"
                                style="background-image:url('assets/media/avatars/300-9.jpg')"></div>
                            <!--end::Photo-->
                            <!--begin::Person-->
                            <div class="mb-0">
                                <!--begin::Name-->
                                <a href="#" class="text-dark fw-bolder text-hover-primary fs-3">Carles Puyol</a>
                                <!--end::Name-->
                                <!--begin::Position-->
                                <div class="text-muted fs-6 fw-bold mt-1">QA Managers</div>
                                <!--begin::Position-->
                            </div>
                            <!--end::Person-->
                        </div>
                        <!--end::Item-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Button-->
                    <button class="btn btn-icon btn-active-color-primary" id="kt_team_slider_prev">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr074.svg-->
                        <span class="svg-icon svg-icon-3x">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <path
                                    d="M11.2657 11.4343L15.45 7.25C15.8642 6.83579 15.8642 6.16421 15.45 5.75C15.0358 5.33579 14.3642 5.33579 13.95 5.75L8.40712 11.2929C8.01659 11.6834 8.01659 12.3166 8.40712 12.7071L13.95 18.25C14.3642 18.6642 15.0358 18.6642 15.45 18.25C15.8642 17.8358 15.8642 17.1642 15.45 16.75L11.2657 12.5657C10.9533 12.2533 10.9533 11.7467 11.2657 11.4343Z"
                                    fill="currentColor" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </button>
                    <!--end::Button-->
                    <!--begin::Button-->
                    <button class="btn btn-icon btn-active-color-primary" id="kt_team_slider_next">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                        <span class="svg-icon svg-icon-3x">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <path
                                    d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                    fill="currentColor" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </button>
                    <!--end::Button-->
                </div>
                <!--end::Slider-->
            </div> --}}
            <!--end::Team-->
            <!--begin::Card-->
            {{-- <div class="card mb-4 bg-light text-center">
                <!--begin::Body-->
                <div class="card-body py-12">
                    <!--begin::Icon-->
                    <a href="#" class="mx-4">
                        <img src="assets/media/svg/brand-logos/facebook-4.svg" class="h-30px my-2" alt="" />
                    </a>
                    <!--end::Icon-->
                    <!--begin::Icon-->
                    <a href="#" class="mx-4">
                        <img src="assets/media/svg/brand-logos/instagram-2-1.svg" class="h-30px my-2" alt="" />
                    </a>
                    <!--end::Icon-->
                    <!--begin::Icon-->
                    <a href="#" class="mx-4">
                        <img src="assets/media/svg/brand-logos/github.svg" class="h-30px my-2" alt="" />
                    </a>
                    <!--end::Icon-->
                    <!--begin::Icon-->
                    <a href="#" class="mx-4">
                        <img src="assets/media/svg/brand-logos/behance.svg" class="h-30px my-2" alt="" />
                    </a>
                    <!--end::Icon-->
                    <!--begin::Icon-->
                    <a href="#" class="mx-4">
                        <img src="assets/media/svg/brand-logos/pinterest-p.svg" class="h-30px my-2" alt="" />
                    </a>
                    <!--end::Icon-->
                    <!--begin::Icon-->
                    <a href="#" class="mx-4">
                        <img src="assets/media/svg/brand-logos/twitter.svg" class="h-30px my-2" alt="" />
                    </a>
                    <!--end::Icon-->
                    <!--begin::Icon-->
                    <a href="#" class="mx-4">
                        <img src="assets/media/svg/brand-logos/dribbble-icon-1.svg" class="h-30px my-2" alt="" />
                    </a>
                    <!--end::Icon-->
                </div>
                <!--end::Body-->
            </div> --}}
            <!--end::Card-->
        </div>
        <!--end::Body-->
    </div>
    <!--end::About card-->
</div>
<!--end::Container-->
</div>
<!--end::Post-->
</div>
@endsection