@extends('layouts.master', ['title' => 'Data Pembayaran'])
@push('css')
<style>
    .card-information {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-information .mb-3 {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-information .fw-bold {
        width: 30%;
    }

    .card-information span {
        width: 60%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .card-information .fw-bold {
            width: 40%;
        }

        .card-information span {
            width: 50%;
        }
    }
</style>

@endpush
@section('content')
<!--begin::Content-->
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Pembayaran</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('bill.index') }}">
                        <li class="text-muted">
                            Data Pembayaran
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        Pembayaran Siswa
                    </li>
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
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">

                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <!--begin::Input group-->
                            <div class="fv-row mb-7 d-flex align-items-center">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3 me-3" for="name">
                                    <span class="required">NIS/NISN/Nama</span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <x-form.student :value="@$tahfidz->student_id" name="student_id"
                                    class="form-control form-control-solid flex-grow-1" />
                                <!--end::Input-->
                                <button id="btn-cari" class="btn btn-sm btn-primary ms-3"
                                    onclick="changeButtonText(this)">
                                    <span class="indicator-label" id="buttonText">Cari</span>
                                    <span class="indicator-progress d-none">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Separator-->
                            <div class="separator mb-6"></div>
                            <!--end::Separator-->
                            <!--begin::Action buttons-->
                            <div class="d-flex justify-content-end">
                            </div>
                            <!--end::Form-->
                            <div id="show-bill"></div>

                        </div>

                        <!--end::Card body-->
                    </div>
                    <!--end::Contacts-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Contacts App- Add New Contact-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $(document).ready(function () {
        function changeButtonText(button) {
            var buttonText = button.find('.indicator-label');
            var indicatorProgress = button.find('.indicator-progress');

            if (buttonText.text() === 'Cari') {
                buttonText.text('Mencari...');
                indicatorProgress.removeClass('d-none');
            } else {
                buttonText.text('Cari');
                indicatorProgress.addClass('d-none');
            }
        }

        $('#btn-cari').click(function () {
            var student_id = $('#student_id').val();
            var url = "{{ route('bill.get-bill-data') }}";
            url = url + '?student_id=' + student_id;

            var button = $(this);
            changeButtonText(button);

            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                    $('#show-bill').html(data);
                    changeButtonText(button); // Reset button text after successful AJAX request
                },
                error: function () {
                    changeButtonText(button); // Reset button text in case of AJAX error
                }
            });
        });

        // on click modal-pay button to show modal and load data
        $(document).on('click', '.modal-pay', function () {
            var url = $(this).data('url');
            var modal = $('#modal-pay');

            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                    modal.find('.modal-body').html(data);
                    modal.modal('show');
                }
            });
        });

        
    });
</script>
@endpush