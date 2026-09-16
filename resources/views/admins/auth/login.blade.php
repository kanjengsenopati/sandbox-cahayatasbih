<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
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
{{--
<link rel="shortcut icon" href="assets/media/logos/favicon.png" /> --}}
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
<link rel="icon" type="imageassets/media/logos/favicon/png" sizes="192x192"
    href="assets/media/logos/favicon/android-icon-192x192.png">
<link rel="icon" type="imageassets/media/logos/favicon/png" sizes="32x32"
    href="assets/media/logos/favicon/favicon-32x32.png">
<link rel="icon" type="imageassets/media/logos/favicon/png" sizes="96x96"
    href="assets/media/logos/favicon/favicon-96x96.png">
<link rel="icon" type="imageassets/media/logos/favicon/png" sizes="16x16"
    href="assets/media/logos/favicon/favicon-16x16.png">
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
                    <!--begin::Form-->
                    <x-alert.alert-validation />
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form"
                        action="{{ route('authenticate') }}" method="POST">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <!--begin::Title-->
                            <h1 class="text-dark mb-3">Assalamualaikum</h1>
                            <span class="text-muted fw-bold fs-5">Silahkan Login Terlebih Dahulu</span>
                        </div>
                        <!--begin::Heading-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="form-label fs-6 fw-bolder text-dark">Email</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input class="form-control form-control-lg form-control-solid" type="email"
                                placeholder="Masukkan Email Yang Terdaftar" name="email" autocomplete="off"
                                value="{{ old('email') }}" />
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
                            <div class="position-relative">
                                <input class="form-control form-control-lg form-control-solid pe-20" type="password"
                                    placeholder="Password Yang Terdaftar" id="password" name="password" autocomplete="off" />
                                <span class="btn btn-sm btn-icon position-absolute translate-middle-y top-50 end-0 me-8" onclick="togglePassword()" type="button" style="cursor: pointer; z-index: 10;">
                                    <i id="eye-icon" class="fas fa-eye text-gray-500 fs-4"></i>
                                </span>
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
    <script>
        var hostUrl = "assets/";
    </script>
    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <!--end::Javascript-->
    @if (session('error'))
    <script>
        Swal.fire({
                title: 'Gagal',
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
    @if (session('info'))
    <script>
        Swal.fire({
                title: 'Informasi',
                text: '{{ session('info') }}',
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
                            'email': {
                                validators: {
                                    notEmpty: {
                                        message: 'Email harus diisi'
                                    },
                                    emailAddress: {
                                        message: 'Format email tidak sesuai'
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

                            // Submit form
                            form.submit();

                            // Disable button shortly after form submission to prevent double clicks without blocking form submission
                            setTimeout(function() {
                                submitButton.disabled = true;
                            }, 50);
                        } else {
                            Swal.fire({
                                text: "Maaf, sepertinya ada beberapa input yang belum sesuai, silahkan cek kembali.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Baik, Saya Mengerti!",
                                customClass: {
                                    confirmButton: "btn btn-success"
                                }
                            });
                        }
                    });
                });

                // Enter key handling: Enter on email focuses password, Enter on password submits
                var emailInput = form.querySelector('input[name="email"]');
                var passwordInput = form.querySelector('input[name="password"]');

                if (emailInput && passwordInput) {
                    emailInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            passwordInput.focus();
                            passwordInput.select();
                        }
                    });

                    passwordInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            submitButton.click();
                        }
                    });
                }
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

        function resetSubmitButton() {
            var btn = document.querySelector('#kt_sign_in_submit');
            if (btn) {
                btn.removeAttribute('data-kt-indicator');
                btn.disabled = false;
            }
        }

        // On document ready & pageshow (BFCache / reload)
        KTUtil.onDOMContentLoaded(function() {
            resetSubmitButton();
            KTSigninGeneral.init();
        });

        window.addEventListener('pageshow', function() {
            resetSubmitButton();
        });

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
<!--end::Body-->

</html>