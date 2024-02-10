@extends('layouts.master', ['title' => 'Bayar Tagihan'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Bayar Tagihan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a class="text-muted text-hover-primary">Data Pembayaran</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Bayar Tagihan</li>
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
            <!--begin::Layout-->
            <div class="d-flex flex-column flex-lg-row">
                <!--begin::Content-->
                <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
                    <!--begin::Form-->
                    <form class="form" action="#" id="kt_subscriptions_create_new">

                        <!--begin::Pricing-->
                        <div class="card card-flush pt-3 mb-5 mb-lg-10">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2 class="fw-bolder">Pembayaran Tagihan</h2>
                                </div>
                                <!--begin::Card title-->
                                <!--begin::Card toolbar-->
                                {{-- <div class="card-toolbar">
                                    <button type="button" class="btn btn-light-primary" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_add_product">Add Product</button>
                                </div> --}}
                                <!--end::Card toolbar-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Table wrapper-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-dashed fs-6 fw-bold gy-4"
                                        id="kt_subscription_products_table">
                                        <!--begin::Table head-->
                                        <thead>
                                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                                <th class="min-w-125px">Bulan</th>
                                                <th class="min-w-125px">Tagihan</th>
                                                <th class="min-w-125px">Tanggal Bayar</th>
                                                <th class="min-w-125px">Metode Pembayaran</th>
                                                <th class="min-w-125px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <!--end::Table head-->
                                        <!--begin::Table body-->
                                        <tbody class="text-gray-600">
                                            @foreach (range(1, 12) as $month)
                                            <tr>
                                                <th class="min-w-125px">
                                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                                </th>
                                                @php
                                                $billForMonth = $bills->where('month', $month)->first();
                                                $amount = $billForMonth ? number_format($billForMonth->amount, 0, ',',
                                                '.') : 0;
                                                @endphp
                                                <input type="hidden" id="bill_id" name="bill_id"
                                                    value="{{ $billForMonth->id ?? '' }}" />
                                                <input type="hidden" id="pay_amount" name="pay_amount"
                                                    value="{{ $billForMonth->amount ?? '' }}" />
                                                <input type="hidden" id="student_id" name="student_id"
                                                    value="{{ $student->id ?? '' }}" />
                                                <td>Rp {{ $amount ?? '' }}</td>
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')
                                                    <span class="badge badge-light-success">
                                                        {{ $billForMonth->transactions?->first()->paid_at
                                                        ?? '' }}
                                                    </span>
                                                    @elseif ($billForMonth)
                                                    <input type="datetime-local" class="form-control date-input"
                                                        name="date" value="{{ now()->format('Y-m-d\TH:i') }}" />
                                                </td>
                                                @endif
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')
                                                    <span class="badge badge-light-success">
                                                        {{ $billForMonth->transactions?->first()->paymentMethod?->name
                                                        ?? ''
                                                        }}
                                                    </span>
                                                    @elseif ($billForMonth)
                                                    <select name="payment_method_id"
                                                        class="form-select payment-method-select">
                                                        @foreach($paymentMethods as $paymentMethod)
                                                        <option value="{{ $paymentMethod->id }}">
                                                            {{ $paymentMethod->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')
                                                    <span class="badge badge-light-success">Sudah Dibayar</span>
                                                    @elseif ($billForMonth && $billForMonth->status == 'UNPAID' &&
                                                    $billForMonth?->transactions?->first()?->payment_link)
                                                    <a href="{{ $billForMonth?->transactions?->first()?->payment_link }}"
                                                        class="btn btn-primary">Ke Halaman Pembayaran</a>
                                                    @elseif ($billForMonth)
                                                    <button type="button"
                                                        class="btn btn-primary bayar-btn">Bayar</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>

                                        <!--end::Table body-->
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table wrapper-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Pricing-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
                <!--begin::Sidebar-->
                <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-300px mb-10 order-1 order-lg-2">
                    <!--begin::Card-->
                    <div class="card card-flush pt-3 mb-0" data-kt-sticky="true"
                        data-kt-sticky-name="subscription-summary" data-kt-sticky-offset="{default: false, lg: '200px'}"
                        data-kt-sticky-width="{lg: '250px', xl: '300px'}" data-kt-sticky-left="auto"
                        data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>Ringkasan</h2>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0 fs-6">
                            <!--begin::Section-->
                            <div class="mb-7">
                                <!--begin::Title-->
                                <h5 class="mb-3">Informasi Siswa</h5>
                                <!--end::Title-->
                                <!--begin::Details-->
                                <div class="d-flex align-items-center mb-1">
                                    <!--begin::Name-->
                                    <span class="fw-bold text-gray-600 me-2">Nama :</span>
                                    <span class="fw-bold text-gray-600 text-hover-primary">{{ @$student->name ?? ''
                                        }}</span>
                                    <!--end::Name-->

                                </div>
                                <!--end::Details-->
                                <!--begin::Email-->
                                <div class="d-flex align-items-center mb-1">
                                    <span class="fw-bold text-gray-600 me-2">Kelas :</span>
                                    <span class="fw-bold text-gray-600 text-hover-primary">{{
                                        @$student->classroom->name
                                        ?? '' }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="fw-bold text-gray-600 me-2">NISN :</span>
                                    <span class="fw-bold text-gray-600 text-hover-primary">{{ @$student->nisn ?? ''
                                        }}</span>
                                </div>
                                <!--end::Email-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Seperator-->
                            <div class="separator separator-dashed mb-7"></div>
                            <!--end::Seperator-->
                            <!--begin::Section-->
                            <div class="mb-10">
                                <!--begin::Title-->
                                <h5 class="mb-3">Informasi Tagihan</h5>
                                <!--end::Title-->
                                <!--begin::Details-->
                                <div class="mb-0">
                                    <!--begin::Card info-->
                                    <div class="fw-bold text-gray-600 d-flex align-items-center">{{ @$billType->name ??
                                        '' }}
                                        <img src="assets/media/svg/card-logos/mastercard.svg" class="w-35px ms-2"
                                            alt="" />
                                    </div>
                                    <!--end::Card info-->
                                    <!--begin::Card expiry-->
                                    <div class="fw-bold text-gray-600">{{ @$billType->academicYear->name ?? '' }}</div>
                                    <!--end::Card expiry-->
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Actions-->
                            <div class="mb-0">
                                <a class="btn btn-primary" href="{{ route('bill.index') }}"> Kembali </a>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Sidebar-->
            </div>
            <!--end::Layout-->
            <!--begin::Modals-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const bayarButtons = document.querySelectorAll('.bayar-btn');
        bayarButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                const row = event.target.closest('tr');
                const dateInput = row.querySelector('.date-input');
                const paymentMethodSelect = row.querySelector('.payment-method-select');
                const billId = row.querySelector('#bill_id').value;
                const payAmount = row.querySelector('#pay_amount').value;

                const loaderHtml = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
                
                // Add loader to the button
                button.innerHTML = loaderHtml + ' Mohon tunggu sebentar';
                button.disabled = true; // Disable button during processing

                const formData = new FormData();
                formData.append('date', dateInput.value);
                formData.append('payment_method_id', paymentMethodSelect.value);
                formData.append('bill_id', billId);
                formData.append('pay_amount', payAmount);
                formData.append('student_id', row.querySelector('#student_id').value);

                axios.post("{{ route('transaction.store') }}", formData, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'multipart/form-data', // Set content type to multipart/form-data
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    // Log response data to console
                    console.log(response.data);

                    // Check if payment is successful
                    if (response.data) {
                        // If payment method is a certain type, redirect to payment link
                        if (response.data.data) {
                            window.location.href = response.data.data;
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    } else {
                        // Handle unsuccessful payment
                        console.error('Error:', response.data.message);
                    }
                })
                .catch(error => {
                    // Handle errors
                    console.error('Error:', error);
                });
            });
        });
    });
</script>
@endpush