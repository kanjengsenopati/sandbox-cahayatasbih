<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Login | {{ env('APP_NAME') }}</title>
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

    <link rel="canonical" href="{{ env('APP_URL') }}" />
    {{-- start favicon --}}
    <link rel="apple-touch-icon" sizes="57x57" href="assets/media/logos/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="assets/media/logos/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="assets/media/logos/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="assets/media/logos/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="assets/media/logos/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="assets/media/logos/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="assets/media/logos/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/media/logos/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/media/logos/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/media/logos/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/media/logos/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/media/logos/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/media/logos/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/media/logos/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="assets/media/logos/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    {{-- end favicon --}}

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    {{-- add custom css --}}
    <style>
        /* change primary color to green */
        .btn-primary {
            background-color: rgb(199, 57, 255) !important;
            border-color: #0BB783 !important;
        }

        /* on select button change to yellow */
        .btn-primary:hover {
            background-color: #F6C23E !important;
            border-color: #F6C23E !important;
        }

        /* Custom styles for the info card */
        .info-card {
            background-color: #f7f7f7;
            color: #6c757d;
            padding: 15px;
            border-radius: 5px;
        }

        .info-card h2 {
            font-size: 1.25rem;
            /* Slightly smaller font size */
            color: #6c757d;
            /* Slightly faded color */
            margin-bottom: 0;
        }
    </style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="bg-body">
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Authentication - Sign-in -->
        <div class="d-flex w-100 flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed"
            style="background-image: url({{ asset('assets/media/bg/login-1.png') }});background-size: cover;">
            <!--begin::Content-->
            <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
                <!--begin::Logo-->
                <a href="#" class="mb-12">
                    <img alt="Logo" src="{{ asset('assets\media\logos\logo-full.png') }}" class="h-75px" />
                </a>
                <!--end::Logo-->
                <!--begin::Wrapper-->
                <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                    <div class="text-center mb-10">
                        <div class="info-card">
                            <h2>Login sebagai Wali Santri</h2>
                        </div>
                    </div>

                    <!--begin::Form-->
                    <x-alert.alert-validation />
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form"
                        action="{{ route('wali.authenticate') }}" method="POST">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <!--begin::Title-->
                            {{-- <h1 class="text-dark mb-3">Silahkan Masuk</h1> --}}
                            <span class="text-muted fw-bold fs-5">Belum punya akun?
                                <a href="{{ route('wali.register') }}" class="link-primary fw-bolder">Daftar
                                    Sekarang</a></span>

                        </div>
                        <!--begin::Heading-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="form-label fs-6 fw-bolder text-dark" for="phone">No Handphone</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input id="phone" class="form-control form-control-lg form-control-solid" type="number"
                                placeholder="Contoh: 081234567890" name="phone" autocomplete="tel"
                                value="{{ old('phone') }}" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-10">
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack mb-2">
                                <!--begin::Label-->
                                <label class="form-label fw-bolder text-dark fs-6 mb-0">Password</label>
                                <!--end::Label-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Input-->
                            <div class="input-group input-group-lg input-group-solid">
                                <input class="form-control form-control-lg form-control-solid" type="password"
                                    placeholder="Password Yang Terdaftar" name="password" autocomplete="off" />
                                <!--begin::Show/hide password-->
                                <div class="input-group-text">
                                    <span class="password-show">
                                        <i class="bi bi-eye-slash fs-2"></i>
                                    </span>
                                    <span class="password-hide d-none">
                                        <i class="bi bi-eye fs-2"></i>
                                    </span>
                                </div>
                                <!--end::Show/hide password-->
                            </div>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Actions-->
                        <div class="text-center">
                            <!--begin::Submit button-->
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
                                <span class="indicator-label">Masuk sekarang</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Content-->
            <!--begin::Footer-->
            <!--end::Footer-->
        </div>
        <!--end::Authentication - Sign-in-->
    </div>
    <!--end::Root-->
    <!--end::Main-->
    <!--begin::Javascript-->
    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Function to toggle password visibility
    var togglePassword = function() {
        var password = document.querySelector('.password-show');
        var passwordHide = document.querySelector('.password-hide');
        var input = document.querySelector('input[type="password"]');

        password.addEventListener('click', function() {
            input.setAttribute('type', 'text');
            passwordHide.classList.remove('d-none');
            password.classList.add('d-none');
        });

        passwordHide.addEventListener('click', function() {
            input.setAttribute('type', 'password');
            password.classList.remove('d-none');
            passwordHide.classList.add('d-none');
        });
    }

    // Call the function to initialize
    togglePassword();
    </script>
    <!--end::Global Javascript Bundle-->
    @if (session('error'))
    <script>
        Swal.fire({
                title: 'Error',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'Ok'
            })
    </script>
    @endif
    @if (session('warning'))
    <script>
        Swal.fire({
                title: 'Peringatan',
                text: '{{ session('warning') }}',
                icon: 'warning',
                confirmButtonText: 'Ok',
                customClass: {
                    confirmButton: "btn fw-bold btn-success"
                }
            })
    </script>
    @endif
    @if (session('success'))
    <script>
        Swal.fire({
                title: 'Berhasil',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Ok'
            })
    </script>
    @endif
    <script>
        "use strict";

        // Class definition
        var KTSigninGeneral = function() {
            // Elements
            var form;
            var submitButton;
            var validator;

            // Handle form
            var handleForm = function(e) {
                validator = FormValidation.formValidation(
                    form, {
                        fields: {
                            'phone': {
                                validators: {
                                    notEmpty: {
                                        message: 'No Handphone harus diisi'
                                    },
                                }
                            },
                            'password': {
                                validators: {
                                    notEmpty: {
                                        message: 'Password harus diisi'
                                    }
                                }
                            }
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: '.fv-row'
                            })
                        }
                    }
                );

                // Handle form submit
                submitButton.addEventListener('click', function(e) {
                    // Prevent button default action
                    e.preventDefault();

                    // Validate form
                    validator.validate().then(function(status) {
                        if (status == 'Valid') {
                            // Show loading indication
                            submitButton.setAttribute('data-kt-indicator', 'on');

                            // Disable button to avoid multiple click
                            submitButton.disabled = true;

                            // Submit form
                            form.submit();
                        } else {
                            Swal.fire({
                                text: "Maaf, sepertinya ada beberapa input yang belum sesuai, silahkan cek kembali.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Baik, Saya Mengerti!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    });
                });
            }

            // Public functions
            return {
                // Initialization
                init: function() {
                    form = document.querySelector('#kt_sign_in_form');
                    submitButton = document.querySelector('#kt_sign_in_submit');

                    handleForm();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTSigninGeneral.init();
        });
    </script>
    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>