<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ env('APP_NAME') }} {{ @$title ? '| ' . $title : '' }}</title>
<meta charset="utf-8" />
<meta name="description" content="{{ env('APP_NAME') }}">
<meta name="author" content="{{ env('APP_NAME') }}">
<meta name="robots" content="noindex, nofollow">

<!-- Open Graph Meta -->
<meta property="og:title" content="{{ env('APP_NAME') }}">
<meta property="og:site_name" content="{{ env('APP_NAME') }}">
<meta property="og:description" content="{{ env('APP_NAME') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="">
<meta property="og:image" content="">

<link rel="canonical" href="{{ env('APP_NAME') }}" />
<link rel="shortcut icon" href="assets/media/logos/logo.png" />
<!--begin::Fonts-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="bg-body">
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Authentication - Signup Free Trial-->
        <div class="d-flex flex-column flex-xl-row flex-column-fluid">
            <!--begin::Aside-->
            <div class="d-flex flex-column flex-center flex-lg-row-fluid">
                <!--begin::Content-->
                <div class="d-flex align-items-start flex-column p-5 p-lg-15">
                    <!--begin::Logo-->
                    <a href="" class="mb-15">
                        <img alt="Logo" src="{{ asset('assets/media/logos/logo.png') }}" class="h-80px" />
                        {{-- <img alt="Logo" src="{{ asset('assets\media\illustrations\signup\signup.svg') }}"
                            class="h-80px" /> --}}
                    </a>
                    <!--end::Logo-->
                    <!--begin::Title-->
                    <h1 class="text-dark fs-2x mb-3">Selamat Datang,<br />
                        Silahkan Mendaftar Akun Anda</h1>
                    <!--end::Title-->
                    <!--begin::Description-->
                    <div class="fw-bold fs-4 text-gray-400 mb-10">Silahkan buat akun untuk mengakses aplikasi kami.
                    </div>
                    <!--begin::Description-->
                    <!--begin::Illustration-->
                    <img src="{{ asset('assets\media\illustrations\signup\signup.svg') }}"
                        class="h-250px h-lg-350px mx-auto" />
                    <!--end::Illustration-->
                </div>
                <!--end::Content-->
            </div>
            <!--begin::Aside-->
            <!--begin::Content-->
            <div class="flex-row-fluid d-flex flex-center justify-content-xl-first p-10">
                <!--begin::Wrapper-->
                <div class="d-flex flex-center p-15 shadow-sm bg-body rounded w-100 w-md-550px mx-auto ms-xl-20">
                    <!--begin::Form-->
                    <form class="form" novalidate="novalidate" id="kt_free_trial_form" method="POST"
                        action="{{ route('wali.register.store') }}">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <!--begin::Title-->
                            <h1 class="text-dark mb-3">Buat Akun</h1>
                            <!--end::Title-->
                            <!--begin::Link-->
                            <div class="text-gray-400 fw-bold fs-4">Sudah punya akun?
                                <a href="{{ route('wali.login') }}" class="link-primary fw-bolder">Masuk</a>
                            </div>
                            <!--end::Link-->
                        </div>
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li class="text-danger">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <!--begin::Heading-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-10">
                            <label class="form-label fw-bolder text-dark fs-6">Nama Lengkap</label>
                            <input class="form-control form-control-solid" type="text" placeholder="Nama Lengkap Anda"
                                name="name" autocomplete="off" value="{{ old('name') }}" />
                        </div>

                        <div class="fv-row mb-10">
                            <label class="form-label fw-bolder text-dark fs-6">No Handphone (WA) Format
                                08xxxxxxxxxx</label>
                            <input class="form-control form-control-solid" type="number" placeholder="08xxxxxxxxxx"
                                name="phone" autocomplete="off" value="{{ old('phone') }}" />
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-7 fv-row" data-kt-password-meter="true">
                            <!--begin::Wrapper-->
                            <div class="mb-1">
                                <!--begin::Label-->
                                <label class="form-label fw-bolder text-dark fs-6">Password</label>
                                <!--end::Label-->
                                <!--begin::Input wrapper-->
                                <div class="position-relative mb-3">
                                    <input class="form-control form-control-solid" type="password" placeholder=""
                                        name="password" autocomplete="off" />
                                    <span
                                        class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                        data-kt-password-meter-control="visibility">
                                        <i class="bi bi-eye-slash fs-2"></i>
                                        <i class="bi bi-eye fs-2 d-none"></i>
                                    </span>
                                </div>
                                <!--end::Input wrapper-->
                                <!--begin::Meter-->
                                <div class="d-flex align-items-center mb-3" data-kt-password-meter-control="highlight">
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
                                </div>
                                <!--end::Meter-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Hint-->
                            <div class="text-muted">Gunakan 8 atau lebih karakter dengan kombinasi huruf, angka &
                                simbol.</div>
                            <!--end::Hint-->
                        </div>
                        <!--end::Input group=-->
                        <!--begin::Row-->
                        <div class="fv-row mb-10">
                            <label class="form-label fw-bolder text-dark fs-6">Confirm Password</label>
                            <input class="form-control form-control-solid" type="password" placeholder=""
                                name="password_confirmation" autocomplete="off" />
                        </div>
                        <!--end::Row-->
                        <!--begin::Row-->
                        <div class="fv-row mb-10">
                            <label class="form-check form-check-custom form-check-solid form-check-inline mb-5">
                                <input class="form-check-input" type="checkbox" name="toc" value="1" />
                                <span class="form-check-label fw-bold text-gray-700">Saya setuju dengan
                                    <a href="#" class="link-primary ms-1">Syarat & Ketentuan</a>.</span>
                            </label>
                        </div>
                        <!--end::Row-->
                        <!--begin::Row-->
                        <div class="text-center pb-lg-0 pb-8">
                            <button type="submit" id="kt_free_trial_submit" class="btn btn-lg btn-primary fw-bolder">
                                <span class="indicator-label">Buat Akun</span>
                                <span class="indicator-progress">Mohon Tunggu...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Row-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Right Content-->
        </div>
        <!--end::Authentication - Signup Free Trial-->
    </div>
    <!--end::Root-->
    <!--end::Main-->
    <!--begin::Javascript-->
    <script>
        var hostUrl = "assets/";
    </script>
    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->
    <!--begin::Page Custom Javascript(used by this page)-->
    {{-- <script src="{{ asset('assets/js/custom/authentication/sign-up/free-trial.js') }}"></script> --}}
    <!--end::Page Custom Javascript-->
    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>