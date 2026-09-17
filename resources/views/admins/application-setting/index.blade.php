@extends('layouts.master', ['title' => 'Setting Aplikasi'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Setting Aplikasi</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('application-setting.index') }}">
                        <li class="breadcrumb-item text-muted">Setting Aplikasi</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        <span class="text-muted fw-bolder fs-7">Edit Setting</span>
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
        <div id="kt_content_container" class="container-fluid">
            @include('admins.partials.tabs-aplikasi')
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->

                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-7">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form action="{{ route('application-setting.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />

                                {{-- add icon kontak aplikasi --}}
                                <div class="row mb-6">
                                    <div class="col-6">
                                        <a href="{{ route('contact.index') }}" class="btn btn-sm btn-light-primary">
                                            <i class="fas fa-user-circle fs-1 text-primary"></i>Data Kontak Aplikasi
                                        </a>
                                    </div>
                                </div>
                                <div class="row mb-6 d-none">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="payment_fee">
                                            <span class="required">Fee Xendit</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Fee Pembayaran"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control form-control-solid input-money"
                                                id="payment_fee" name="payment_fee" placeholder="Masukkan Fee Xendit"
                                                value="{{ isset($applicationSetting->payment_fee) ? number_format($applicationSetting->payment_fee, 0, ',', '.') : (old('payment_fee') ? number_format(old('payment_fee'), 0, ',', '.') : 0) }}" />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="bill_fee">
                                            <span class="required">Fee Tagihan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Fee Pembayaran"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control form-control-solid input-money" id="bill_fee"
                                                name="bill_fee" placeholder="Masukkan Fee Tagihan"
                                                value="{{ isset($applicationSetting->bill_fee) ? number_format($applicationSetting->bill_fee, 0, ',', '.') : (old('bill_fee') ? number_format(old('bill_fee'), 0, ',', '.') : 0) }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-6 d-none">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="saldo_fee">
                                            <span class="required">Fee Saldo</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Fee Pembayaran"></i>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control form-control-solid" id="saldo_fee"
                                                name="saldo_fee" placeholder="Masukkan Fee Saldo"
                                                value="{{ @$applicationSetting->saldo_fee ?? old('saldo_fee') ?? 0 }}" />
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="payment_expire_time">
                                            <span class="required">Waktu Kadaluarsa Pembayaran</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Waktu Kadaluarsa Pembayaran"></i>
                                        </label>
                                        <input type="text" placeholder="hh:mm"
                                            class="form-control form-control-solid time" id="payment_expire_time"
                                            name="payment_expire_time"
                                            placeholder="Masukkan Waktu Kadaluarsa Pembayaran"
                                            value="{{ @$applicationSetting->payment_expire_time ?? old('payment_expire_time') ?? '24:00' }}" />
                                    </div>
                                </div>

                                {{-- <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="target_month">
                                            <span class="required">Target Bulanan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Target Bulanan"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control form-control-solid input-money"
                                                id="target_month" name="target_month"
                                                placeholder="Masukkan Target Pembayaran Bulanan"
                                                value="{{ @$applicationSetting->target_month ?? old('target_month') }}"
                                                required />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="target_year">
                                            <span class="required">Target Tahunan</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Target Tahunan"></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control form-control-solid input-money"
                                                id="target_year" name="target_year"
                                                placeholder="Masukkan Target Pembayaran Tahunan"
                                                value="{{ @$applicationSetting->target_year ?? old('target_year') }}"
                                                required />
                                        </div>
                                    </div>
                                </div> --}}

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="link_whatsapp">
                                            <span class="required">Link Whatsapp Gateway</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Link Whatsapp Gateway"></i>
                                        </label>
                                        <input type="url" class="form-control form-control-solid" id="link_whatsapp"
                                            name="link_whatsapp" placeholder="Masukkan Link Whatsapp Gateway"
                                            value="{{ @$applicationSetting->link_whatsapp ?? old('link_whatsapp') }}"
                                            required />
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="number_whatsapp">
                                            <span class="required">Nomor Whatsapp</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nomor Whatsapp Gateway"></i>
                                        </label>
                                        <input type="number" class="form-control form-control-solid"
                                            id="number_whatsapp" name="number_whatsapp"
                                            placeholder="Masukkan Nomor Whatsapp Gateway"
                                            value="{{ @$applicationSetting->number_whatsapp ?? old('number_whatsapp') }}"
                                            required />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="api_key">
                                            <span class="required">Status Device ID</span>
                                            {{-- add info text with color red or green --}}
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Status Device ID"></i>
                                        </label>
                                        {{-- no input only info --}}
                                        <div class="form-control form-control-solid">
                                            <span
                                                class="badge badge-{{ @$applicationSetting->whatsapp_status ? 'success' : 'danger' }}">
                                                {{ @$applicationSetting->whatsapp_status ? 'Aktif' : 'Tidak Aktif'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="device_id">
                                            <span class="required">Device ID</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan device id yang diperolah dari aplikasi whatsapp gateway"></i>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" id="device_id"
                                            name="device_id" placeholder="Masukkan Device ID"
                                            value="{{ @$applicationSetting->device_id ?? old('device_id') }}"
                                            required />
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <!--end::Input-->
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <div class="fv-row mb-6">
                                            <x-form.image-upload label="Background Kartu Siswa" name="student_card_image"
                                                :value="@$applicationSetting->student_card_image ?? null" />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="fs-6 fw-bold form-label" for="payment_auto_check">
                                            <span class="required">Auto-Checking Bukti Transfer (AI)</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Aktifkan fitur deteksi nominal otomatis dengan AI"></i>
                                        </label>
                                        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" name="payment_auto_check" id="payment_auto_check" {{ @$applicationSetting->payment_auto_check ? 'checked' : '' }} />
                                            <label class="form-check-label fw-bold text-gray-400 ms-3" for="payment_auto_check">Aktifkan Auto-Checking</label>
                                        </div>
                                        <div class="text-muted fs-7 mt-2">
                                            Jika aktif, sistem akan membaca gambar bukti transfer menggunakan Gemini AI dan otomatis melakukan validasi Lunas/Cek Ulang.
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-12">
                                        <div class="card card-bordered p-4 bg-light-primary border-primary">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <label class="fs-6 fw-bold form-label mb-0" for="allow_pwa_login_wali">
                                                    <span class="required">Akses Login PWA Wali Santri (Global System)</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                        title="Buka atau tutup akses login PWA secara keseluruhan"></i>
                                                </label>
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" value="1" name="allow_pwa_login_wali" id="allow_pwa_login_wali" {{ (!isset($applicationSetting->allow_pwa_login_wali) || $applicationSetting->allow_pwa_login_wali) ? 'checked' : '' }} />
                                                    <label class="form-check-label fw-bold text-dark ms-2" for="allow_pwa_login_wali">Izinkan Login PWA Wali Santri</label>
                                                </div>
                                            </div>
                                            <div class="text-muted fs-7 mb-3">
                                                Jika sakelar ini dimatikan (dinonaktifkan), seluruh upaya login di aplikasi PWA Wali Santri akan ditolak sementara dengan pesan kustom di bawah ini.
                                            </div>
                                            <div>
                                                <label class="fs-7 fw-bold form-label text-gray-700" for="pwa_login_disabled_message">Pesan Kustom Saat Login Dinonaktifkan (Opsional):</label>
                                                <textarea class="form-control form-control-solid fs-7" name="pwa_login_disabled_message" id="pwa_login_disabled_message" rows="2" placeholder="Contoh: Maaf, layanan login PWA Wali Santri sedang ditutup sementara selama Ujian Akhir Semester.">{{ @$applicationSetting->pwa_login_disabled_message ?? old('pwa_login_disabled_message') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-12">
                                        <div class="card card-bordered p-4 bg-light-warning border-warning">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <label class="fs-6 fw-bold form-label mb-0" for="allow_pwa_saldo_payment_wali">
                                                    <span class="required">Pembayaran Tagihan via Saldo (PWA Wali Santri)</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                        title="Buka atau tutup akses pembayaran tagihan menggunakan saldo khusus di PWA Wali"></i>
                                                </label>
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" value="1" name="allow_pwa_saldo_payment_wali" id="allow_pwa_saldo_payment_wali" {{ (!isset($applicationSetting->allow_pwa_saldo_payment_wali) || $applicationSetting->allow_pwa_saldo_payment_wali) ? 'checked' : '' }} />
                                                    <label class="form-check-label fw-bold text-dark ms-2" for="allow_pwa_saldo_payment_wali">Izinkan Pembayaran via Saldo di PWA Wali</label>
                                                </div>
                                            </div>
                                            <div class="text-muted fs-7 mb-3">
                                                Jika sakelar ini dimatikan (dinonaktifkan), seluruh upaya transaksi pembayaran tagihan menggunakan Saldo di PWA Wali Santri akan ditolak sementara dengan pesan kustom di bawah ini. (Transaksi Kasir POS/Kantin tetap berjalan).
                                            </div>
                                            <div>
                                                <label class="fs-7 fw-bold form-label text-gray-700" for="pwa_saldo_payment_disabled_message">Pesan Kustom Saat Pembayaran Saldo PWA Dinonaktifkan (Opsional):</label>
                                                <textarea class="form-control form-control-solid fs-7" name="pwa_saldo_payment_disabled_message" id="pwa_saldo_payment_disabled_message" rows="2" placeholder="Contoh: Pembayaran tagihan menggunakan Saldo di PWA Wali Santri sedang dinonaktifkan sementara oleh Pengelola.">{{ @$applicationSetting->pwa_saldo_payment_disabled_message ?? old('pwa_saldo_payment_disabled_message') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-12">
                                        <div class="card card-bordered p-4 bg-light-info border-info">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-bullhorn fs-3 text-info me-2"></i>
                                                <label class="fs-6 fw-bold form-label mb-0 text-dark" for="pwa_hero_saldo_off_message">
                                                    Pesan Informasi Hero Banner PWA (Saat Saldo Disembunyikan / OFF)
                                                </label>
                                            </div>
                                            <div class="text-muted fs-7 mb-3">
                                                Pesan ini akan tampil secara elegan di dalam kartu profil Hero Banner santri pada PWA Wali Santri ketika visibilitas Saldo diatur ke mode <strong>OFF / Sembunyi</strong> untuk jenjang atau kelas santri tersebut.
                                            </div>
                                            <div>
                                                <textarea class="form-control form-control-solid fs-7" name="pwa_hero_saldo_off_message" id="pwa_hero_saldo_off_message" rows="2" placeholder="Contoh: Layanan uang saku & belanja santri dikelola melalui sistem kartu utama / aplikasi lama.">{{ @$applicationSetting->pwa_hero_saldo_off_message ?? old('pwa_hero_saldo_off_message') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Granular Access & Saldo Visibility Matrix per UPT / Kelas -->
                                <div class="row mb-6">
                                    <div class="col-12">
                                        <div class="card card-bordered border-gray-300 shadow-sm">
                                            <div class="card-header bg-light-primary py-4 px-6 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                <div>
                                                    <h3 class="card-title fw-bolder text-gray-900 fs-5 mb-1">
                                                        <i class="fas fa-layer-group text-primary me-2"></i>Pengaturan Akses PWA & Visibilitas Saldo per UPT / Lembaga & Kelas
                                                    </h3>
                                                    <span class="text-muted fs-7">
                                                        Atur izin login PWA dan opsi <strong>ON/OFF menampilkan UI Saldo</strong> di Kartu Hero & Riwayat Transaksi per Lembaga dan Rombel Kelas.
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-light-success fw-bold" onclick="applyPresetKelas7Only()">
                                                        <i class="fas fa-magic me-1"></i>Preset: Saldo Kelas 7 Saja (Kelas 8-12 OFF)
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light-primary fw-bold" onclick="toggleAllSaldo(true)">
                                                        <i class="fas fa-check-double me-1"></i>Aktifkan Semua Saldo
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light-danger fw-bold" onclick="toggleAllSaldo(false)">
                                                        <i class="fas fa-ban me-1"></i>Nonaktifkan Semua Saldo
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="card-body p-6">
                                                @if(isset($schools) && $schools->count() > 0)
                                                    <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold mb-6" role="tablist">
                                                        @foreach($schools as $index => $school)
                                                            <li class="nav-item" role="presentation">
                                                                <a class="nav-link text-active-primary py-3 me-6 {{ $index === 0 ? 'active' : '' }}" 
                                                                   data-bs-toggle="tab" 
                                                                   href="#tab_school_{{ $school->id }}" 
                                                                   role="tab">
                                                                    <i class="fas fa-school me-2 text-primary"></i>{{ $school->name }} ({{ $school->type ?? 'UPT' }})
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                    <div class="tab-content" id="schoolTabsContent">
                                                        @foreach($schools as $index => $school)
                                                            @php
                                                                // Group class levels in this school for quick selection chips
                                                                $levels = $school->classroom->map(function($c) {
                                                                    if (preg_match('/^(VII|VIII|IX|X{1,2}I{0,2}|I{1,3}V?|[0-9]+)/', strtoupper($c->name), $m)) {
                                                                        return $m[1];
                                                                    }
                                                                    return 'Lainnya';
                                                                })->unique()->filter()->values();
                                                            @endphp
                                                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="tab_school_{{ $school->id }}" role="tabpanel">
                                                                <!-- Master Policy for this School -->
                                                                <div class="p-4 rounded-3 bg-light mb-4 border border-gray-200">
                                                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                                                                        <div>
                                                                            <span class="fs-6 fw-bolder text-gray-800 d-block">Kebijakan Induk Lembaga: {{ $school->name }}</span>
                                                                            <span class="fs-7 text-muted">Pengaturan default yang diwarisi oleh seluruh rombel kelas di lembaga ini.</span>
                                                                        </div>
                                                                        <div class="d-flex align-items-center gap-6 flex-wrap">
                                                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                                                <input class="form-check-input school-login-toggle" type="checkbox" value="1" 
                                                                                    name="schools[{{ $school->id }}][allow_pwa_login]" 
                                                                                    id="school_login_{{ $school->id }}" 
                                                                                    data-school-id="{{ $school->id }}"
                                                                                    {{ (!isset($school->allow_pwa_login) || $school->allow_pwa_login) ? 'checked' : '' }} />
                                                                                <label class="form-check-label fw-bold text-gray-800 fs-7" for="school_login_{{ $school->id }}">Akses Login PWA</label>
                                                                            </div>
                                                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                                                <input class="form-check-input school-saldo-toggle" type="checkbox" value="1" 
                                                                                    name="schools[{{ $school->id }}][show_pwa_saldo]" 
                                                                                    id="school_saldo_{{ $school->id }}" 
                                                                                    data-school-id="{{ $school->id }}"
                                                                                    {{ (!isset($school->show_pwa_saldo) || $school->show_pwa_saldo) ? 'checked' : '' }} />
                                                                                <label class="form-check-label fw-bold text-gray-800 fs-7" for="school_saldo_{{ $school->id }}">Tampilkan UI Saldo</label>
                                                                            </div>
                                                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                                                <input class="form-check-input school-pay-toggle" type="checkbox" value="1" 
                                                                                    name="schools[{{ $school->id }}][allow_pwa_saldo_payment]" 
                                                                                    id="school_pay_{{ $school->id }}" 
                                                                                    data-school-id="{{ $school->id }}"
                                                                                    {{ (!isset($school->allow_pwa_saldo_payment) || $school->allow_pwa_saldo_payment) ? 'checked' : '' }} />
                                                                                <label class="form-check-label fw-bold text-gray-800 fs-7" for="school_pay_{{ $school->id }}">Bayar via Saldo</label>
                                                                            </div>
                                                                            <div class="d-flex align-items-center gap-2 border border-gray-300 rounded p-1 bg-white">
                                                                                <div class="form-check form-switch form-check-custom form-check-solid ms-2">
                                                                                    <input class="form-check-input school-limit-toggle" type="checkbox" value="1" 
                                                                                        name="schools[{{ $school->id }}][is_saldo_limit_active]" 
                                                                                        id="school_limit_active_{{ $school->id }}" 
                                                                                        data-school-id="{{ $school->id }}"
                                                                                        {{ $school->is_saldo_limit_active ? 'checked' : '' }} />
                                                                                    <label class="form-check-label fw-bold text-gray-800 fs-7" for="school_limit_active_{{ $school->id }}">Limit Saldo</label>
                                                                                </div>
                                                                                <input type="text" class="form-control form-control-sm form-control-solid w-125px input-currency school-limit-input" 
                                                                                    name="schools[{{ $school->id }}][saldo_limit]" 
                                                                                    data-school-id="{{ $school->id }}"
                                                                                    placeholder="Rp Maksimal"
                                                                                    value="{{ $school->saldo_limit ? number_format($school->saldo_limit, 0, ',', '.') : '' }}" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Multi-Select Filter Chips & Bulk Action Toolbar -->
                                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                                        <span class="fs-7 fw-bold text-gray-600 me-1">Pilih Cepat:</span>
                                                                        <button type="button" class="btn btn-xs btn-light-primary fw-bold" onclick="selectClassesByLevel('{{ $school->id }}', 'ALL')">
                                                                            <i class="fas fa-check-square me-1"></i>Semua ({{ $school->classroom->count() }})
                                                                        </button>
                                                                        @foreach($levels as $lvl)
                                                                            <button type="button" class="btn btn-xs btn-light-info fw-bold" onclick="selectClassesByLevel('{{ $school->id }}', '{{ $lvl }}')">
                                                                                Kelas {{ $lvl }}
                                                                            </button>
                                                                        @endforeach
                                                                        <button type="button" class="btn btn-xs btn-light-secondary fw-bold" onclick="clearClassSelection('{{ $school->id }}')">
                                                                            <i class="fas fa-times me-1"></i>Batal Pilih
                                                                        </button>
                                                                    </div>
                                                                    <div>
                                                                        <span class="badge badge-light-primary fw-bolder fs-7 px-3 py-2" id="selection_count_badge_{{ $school->id }}">
                                                                            0 rombel dipilih
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <!-- Dynamic Floating Bulk Actions Bar -->
                                                                <div class="bulk-action-bar card card-bordered border-primary bg-light-primary p-3 mb-4 d-none" id="bulk_bar_{{ $school->id }}">
                                                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div class="w-8 h-8 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-7">
                                                                                <i class="fas fa-sliders-h text-white"></i>
                                                                            </div>
                                                                            <div>
                                                                                <div class="fw-bolder text-gray-900 fs-7">Aksi Massal Multi-Select (<span class="selected-num">0</span> kelas)</div>
                                                                                <div class="text-muted fs-8">Pilih opsi di bawah untuk menerapkan perubahan sekaligus pada seluruh baris yang dicentang.</div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                                                            <!-- UI Saldo Bulk Actions -->
                                                                            <div class="d-flex align-items-center gap-1 bg-white p-1 rounded-2 border border-gray-300">
                                                                                <span class="fs-8 fw-bolder text-gray-700 px-2">UI Saldo:</span>
                                                                                <button type="button" class="btn btn-xs btn-success fw-bold" onclick="applyBulkAction('{{ $school->id }}', 'saldo', '1')">
                                                                                    <i class="fas fa-eye me-1"></i>ON (Tampil)
                                                                                </button>
                                                                                <button type="button" class="btn btn-xs btn-danger fw-bold" onclick="applyBulkAction('{{ $school->id }}', 'saldo', '0')">
                                                                                    <i class="fas fa-eye-slash me-1"></i>OFF (Sembunyi)
                                                                                </button>
                                                                                <button type="button" class="btn btn-xs btn-light fw-bold text-gray-700" onclick="applyBulkAction('{{ $school->id }}', 'saldo', '')">
                                                                                    Ikuti Lembaga
                                                                                </button>
                                                                            </div>

                                                                            <!-- PWA Login Bulk Actions -->
                                                                            <div class="d-flex align-items-center gap-1 bg-white p-1 rounded-2 border border-gray-300">
                                                                                <span class="fs-8 fw-bolder text-gray-700 px-2">Login PWA:</span>
                                                                                <button type="button" class="btn btn-xs btn-light-success fw-bold text-success" onclick="applyBulkAction('{{ $school->id }}', 'login', '1')">
                                                                                    <i class="fas fa-check me-1"></i>Aktif
                                                                                </button>
                                                                                <button type="button" class="btn btn-xs btn-light-danger fw-bold text-danger" onclick="applyBulkAction('{{ $school->id }}', 'login', '0')">
                                                                                    <i class="fas fa-ban me-1"></i>Tutup
                                                                                </button>
                                                                                <button type="button" class="btn btn-xs btn-light fw-bold text-gray-700" onclick="applyBulkAction('{{ $school->id }}', 'login', '')">
                                                                                    Ikuti
                                                                                </button>
                                                                            </div>

                                                                            <!-- Bayar Tagihan Bulk Actions -->
                                                                            <div class="d-flex align-items-center gap-1 bg-white p-1 rounded-2 border border-gray-300">
                                                                                <span class="fs-8 fw-bolder text-gray-700 px-2">Bayar Saldo:</span>
                                                                                <button type="button" class="btn btn-xs btn-light-warning fw-bold text-warning" onclick="applyBulkAction('{{ $school->id }}', 'pay', '1')">
                                                                                    Izinkan
                                                                                </button>
                                                                                <button type="button" class="btn btn-xs btn-light-secondary fw-bold text-muted" onclick="applyBulkAction('{{ $school->id }}', 'pay', '0')">
                                                                                    Nonaktif
                                                                                </button>
                                                                                <button type="button" class="btn btn-xs btn-light fw-bold text-gray-700" onclick="applyBulkAction('{{ $school->id }}', 'pay', '')">
                                                                                    Ikuti
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Classrooms Table for this School -->
                                                                <div class="table-responsive">
                                                                    <table class="table table-row-bordered table-row-gray-200 align-middle gs-4 gy-3" id="table_school_{{ $school->id }}">
                                                                        <thead class="bg-light-secondary text-gray-700 fw-bolder fs-7 text-uppercase gs-0">
                                                                            <tr>
                                                                                <th class="w-40px text-center">
                                                                                    <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                                                                                        <input class="form-check-input select-all-classes-check" type="checkbox" 
                                                                                            data-school-id="{{ $school->id }}" 
                                                                                            onchange="toggleSelectAllRows('{{ $school->id }}', this.checked)" />
                                                                                    </div>
                                                                                </th>
                                                                                <th class="min-w-140px">Nama Kelas / Rombel</th>
                                                                                <th class="text-center min-w-140px">Akses Login PWA</th>
                                                                                <th class="text-center min-w-180px">Tampilkan UI Saldo (Hero & Riwayat)</th>
                                                                                <th class="text-center min-w-150px">Bayar Tagihan Saldo</th>
                                                                                <th class="text-center min-w-180px">Limit Saldo Harian</th>
                                                                                <th class="text-end min-w-120px">Status Efektif</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody class="fw-semibold text-gray-600 fs-7">
                                                                            @forelse($school->classroom as $class)
                                                                                @php
                                                                                    $classLevel = '';
                                                                                    if (preg_match('/^(VII|VIII|IX|X{1,2}I{0,2}|I{1,3}V?|[0-9]+)/', strtoupper($class->name), $m)) {
                                                                                        $classLevel = $m[1];
                                                                                    }
                                                                                    $isKelas7 = in_array(strtoupper($classLevel), ['7', 'VII']);
                                                                                @endphp
                                                                                <tr id="row_class_{{ $class->id }}" data-class-level="{{ $classLevel }}" data-school-id="{{ $school->id }}" class="classroom-row">
                                                                                    <td class="text-center">
                                                                                        <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                                                                                            <input class="form-check-input row-class-check" type="checkbox" 
                                                                                                value="{{ $class->id }}"
                                                                                                data-school-id="{{ $school->id }}" 
                                                                                                data-class-id="{{ $class->id }}"
                                                                                                data-class-level="{{ $classLevel }}"
                                                                                                onchange="onRowCheckChange('{{ $school->id }}')" />
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="d-flex align-items-center">
                                                                                            <span class="badge badge-light-primary fw-bolder me-2">{{ $class->name }}</span>
                                                                                            <span class="text-gray-500 fs-8">({{ $class->students_count ?? $class->students()->count() }} santri)</span>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <select class="form-select form-select-sm form-select-solid fw-bold w-130px d-inline-block class-login-select dynamic-state-select" 
                                                                                                name="classrooms[{{ $class->id }}][allow_pwa_login]" 
                                                                                                data-class-id="{{ $class->id }}"
                                                                                                onchange="updateSelectStateColor(this)">
                                                                                            <option value="" {{ is_null($class->allow_pwa_login) ? 'selected' : '' }}>Ikuti Lembaga</option>
                                                                                            <option value="1" {{ $class->allow_pwa_login === true ? 'selected' : '' }}>Aktif (Diizinkan)</option>
                                                                                            <option value="0" {{ $class->allow_pwa_login === false ? 'selected' : '' }}>Nonaktif (Ditutup)</option>
                                                                                        </select>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <select class="form-select form-select-sm form-select-solid fw-bold w-150px d-inline-block class-saldo-select dynamic-state-select" 
                                                                                                name="classrooms[{{ $class->id }}][show_pwa_saldo]" 
                                                                                                data-class-id="{{ $class->id }}"
                                                                                                data-class-level="{{ $classLevel }}"
                                                                                                onchange="updateSelectStateColor(this)">
                                                                                            <option value="" {{ is_null($class->show_pwa_saldo) ? 'selected' : '' }}>Ikuti Lembaga</option>
                                                                                            <option value="1" {{ $class->show_pwa_saldo === true ? 'selected' : '' }}>ON (Tampilkan Saldo)</option>
                                                                                            <option value="0" {{ $class->show_pwa_saldo === false ? 'selected' : '' }}>OFF (Sembunyikan Saldo)</option>
                                                                                        </select>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <select class="form-select form-select-sm form-select-solid fw-bold w-140px d-inline-block class-pay-select dynamic-state-select" 
                                                                                                name="classrooms[{{ $class->id }}][allow_pwa_saldo_payment]" 
                                                                                                data-class-id="{{ $class->id }}"
                                                                                                onchange="updateSelectStateColor(this)">
                                                                                            <option value="" {{ is_null($class->allow_pwa_saldo_payment) ? 'selected' : '' }}>Ikuti Lembaga</option>
                                                                                            <option value="1" {{ $class->allow_pwa_saldo_payment === true ? 'selected' : '' }}>Diizinkan</option>
                                                                                            <option value="0" {{ $class->allow_pwa_saldo_payment === false ? 'selected' : '' }}>Dinonaktifkan</option>
                                                                                        </select>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <div class="d-flex align-items-center gap-2 justify-content-center">
                                                                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                                                                <input class="form-check-input class-limit-toggle" type="checkbox" value="1" 
                                                                                                    name="classrooms[{{ $class->id }}][is_saldo_limit_active]" 
                                                                                                    id="class_limit_active_{{ $class->id }}" 
                                                                                                    data-class-id="{{ $class->id }}"
                                                                                                    {{ $class->is_saldo_limit_active ? 'checked' : '' }} />
                                                                                            </div>
                                                                                            @php
                                                                                                if ($school->is_saldo_limit_active) {
                                                                                                    $formattedSchoolLimit = $school->saldo_limit ? number_format($school->saldo_limit, 0, ',', '.') : '0';
                                                                                                    $placeholderText = 'Mewarisi (' . $formattedSchoolLimit . ')';
                                                                                                } else {
                                                                                                    $placeholderText = 'Maksimal';
                                                                                                }
                                                                                            @endphp
                                                                                            <input type="text" class="form-control form-control-sm form-control-solid w-100px input-currency class-limit-input" 
                                                                                                name="classrooms[{{ $class->id }}][saldo_limit]" 
                                                                                                placeholder="{{ $placeholderText }}"
                                                                                                data-school-id="{{ $school->id }}"
                                                                                                value="{{ $class->saldo_limit ? number_format($class->saldo_limit, 0, ',', '.') : '' }}" />
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        @if($isKelas7)
                                                                                            <span class="badge badge-light-success fw-bold">Aplikasi Baru</span>
                                                                                        @else
                                                                                            <span class="badge badge-light-warning fw-bold">Aplikasi Lama</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @empty
                                                                                <tr>
                                                                                    <td colspan="7" class="text-center text-muted py-4">Belum ada kelas terdaftar di lembaga ini.</td>
                                                                                </tr>
                                                                            @endforelse
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-center py-6 text-muted">Data Lembaga / UPT belum tersedia.</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    // 1. Row Selection Engine
                                    function onRowCheckChange(schoolId) {
                                        const checks = document.querySelectorAll(`#table_school_${schoolId} .row-class-check`);
                                        const checked = document.querySelectorAll(`#table_school_${schoolId} .row-class-check:checked`);
                                        const allCheck = document.querySelector(`.select-all-classes-check[data-school-id="${schoolId}"]`);
                                        const bulkBar = document.getElementById(`bulk_bar_${schoolId}`);
                                        const countBadge = document.getElementById(`selection_count_badge_${schoolId}`);

                                        if (allCheck) {
                                            allCheck.checked = (checks.length > 0 && checks.length === checked.length);
                                            allCheck.indeterminate = (checked.length > 0 && checked.length < checks.length);
                                        }

                                        // Update count text
                                        if (countBadge) {
                                            countBadge.textContent = `${checked.length} rombel dipilih`;
                                            if (checked.length > 0) {
                                                countBadge.className = 'badge badge-primary fw-bolder fs-7 px-3 py-2';
                                            } else {
                                                countBadge.className = 'badge badge-light-primary fw-bolder fs-7 px-3 py-2';
                                            }
                                        }

                                        // Highlight active rows
                                        checks.forEach(chk => {
                                            const tr = document.getElementById(`row_class_${chk.value}`);
                                            if (tr) {
                                                if (chk.checked) {
                                                    tr.classList.add('bg-light-primary');
                                                } else {
                                                    tr.classList.remove('bg-light-primary');
                                                }
                                            }
                                        });

                                        // Show/Hide Floating Bulk Action Bar
                                        if (bulkBar) {
                                            if (checked.length > 0) {
                                                bulkBar.classList.remove('d-none');
                                                bulkBar.querySelectorAll('.selected-num').forEach(el => el.textContent = checked.length);
                                            } else {
                                                bulkBar.classList.add('d-none');
                                            }
                                        }
                                    }

                                    function toggleSelectAllRows(schoolId, isChecked) {
                                        const checks = document.querySelectorAll(`#table_school_${schoolId} .row-class-check`);
                                        checks.forEach(chk => chk.checked = isChecked);
                                        onRowCheckChange(schoolId);
                                    }

                                    function selectClassesByLevel(schoolId, level) {
                                        const checks = document.querySelectorAll(`#table_school_${schoolId} .row-class-check`);
                                        checks.forEach(chk => {
                                            if (level === 'ALL') {
                                                chk.checked = true;
                                            } else {
                                                const rowLevel = (chk.getAttribute('data-class-level') || '').toUpperCase();
                                                chk.checked = (rowLevel === level.toUpperCase());
                                            }
                                        });
                                        onRowCheckChange(schoolId);
                                        toastr.info(`Memilih kelas jenjang ${level === 'ALL' ? 'Semua' : level}`);
                                    }

                                    function clearClassSelection(schoolId) {
                                        const checks = document.querySelectorAll(`#table_school_${schoolId} .row-class-check`);
                                        checks.forEach(chk => chk.checked = false);
                                        onRowCheckChange(schoolId);
                                    }

                                    // 2. Apply Bulk Actions to Selected Rows
                                    function applyBulkAction(schoolId, type, value) {
                                        const checked = document.querySelectorAll(`#table_school_${schoolId} .row-class-check:checked`);
                                        if (checked.length === 0) {
                                            toastr.warning('Silakan pilih minimal 1 rombel kelas terlebih dahulu.');
                                            return;
                                        }

                                        let typeLabel = '';
                                        let valueLabel = '';

                                        checked.forEach(chk => {
                                            const classId = chk.value;
                                            const tr = document.getElementById(`row_class_${classId}`);
                                            if (!tr) return;

                                            let selectEl = null;
                                            if (type === 'saldo') {
                                                selectEl = tr.querySelector('.class-saldo-select');
                                                typeLabel = 'UI Saldo';
                                                valueLabel = value === '1' ? 'ON (Tampilkan Saldo)' : (value === '0' ? 'OFF (Sembunyikan Saldo)' : 'Ikuti Lembaga');
                                            } else if (type === 'login') {
                                                selectEl = tr.querySelector('.class-login-select');
                                                typeLabel = 'Akses Login';
                                                valueLabel = value === '1' ? 'Aktif' : (value === '0' ? 'Nonaktif' : 'Ikuti Lembaga');
                                            } else if (type === 'pay') {
                                                selectEl = tr.querySelector('.class-pay-select');
                                                typeLabel = 'Bayar via Saldo';
                                                valueLabel = value === '1' ? 'Diizinkan' : (value === '0' ? 'Dinonaktifkan' : 'Ikuti Lembaga');
                                            }

                                            if (selectEl) {
                                                selectEl.value = value;
                                                updateSelectStateColor(selectEl); // update color immediately
                                                // Trigger highlight pulse animation
                                                selectEl.classList.add('border-primary');
                                                setTimeout(() => {
                                                    selectEl.classList.remove('border-primary');
                                                }, 1200);
                                            }
                                        });

                                        toastr.success(`Berhasil menyetel ${typeLabel} ➔ "${valueLabel}" untuk ${checked.length} rombel terpilih! Klik "Simpan" di bawah untuk menyimpan.`);
                                    }

                                    // 3. Preset Cepat Global
                                    function applyPresetKelas7Only() {
                                        if (!confirm('Apakah Anda ingin menerapkan Preset: Kelas 7 Saldo ON & Kelas 8, 9, 10, 11, 12 Saldo OFF?')) {
                                            return;
                                        }

                                        document.querySelectorAll('.school-saldo-toggle').forEach(el => {
                                            el.checked = true;
                                        });

                                        document.querySelectorAll('.class-saldo-select').forEach(select => {
                                            const level = (select.getAttribute('data-class-level') || '').toUpperCase();
                                            if (level === '7' || level === 'VII') {
                                                select.value = '1'; // ON
                                            } else if (['8', '9', '10', '11', '12', 'VIII', 'IX', 'X', 'XI', 'XII'].includes(level)) {
                                                select.value = '0'; // OFF
                                            } else {
                                                select.value = '0'; // OFF
                                            }
                                            updateSelectStateColor(select);
                                        });

                                        toastr.success('Preset Kelas 7 Aktif Penuh berhasil dipasang! Klik tombol "Simpan" di bawah untuk menyimpan perubahan.');
                                    }

                                    function toggleAllSaldo(status) {
                                        document.querySelectorAll('.school-saldo-toggle').forEach(el => {
                                            el.checked = status;
                                        });
                                        document.querySelectorAll('.class-saldo-select').forEach(select => {
                                            select.value = status ? '1' : '0';
                                            updateSelectStateColor(select);
                                        });
                                        toastr.info(status ? 'Seluruh UI Saldo diaktifkan.' : 'Seluruh UI Saldo dinonaktifkan.');
                                    }

                                    // 4. State Color UI
                                    function updateSelectStateColor(selectEl) {
                                        selectEl.classList.remove('bg-light-success', 'text-success', 'bg-light-danger', 'text-danger', 'text-gray-600', 'bg-light');
                                        
                                        if (selectEl.value === '1') {
                                            selectEl.classList.add('bg-light-success', 'text-success');
                                        } else if (selectEl.value === '0') {
                                            selectEl.classList.add('bg-light-danger', 'text-danger');
                                        } else {
                                            selectEl.classList.add('bg-light', 'text-gray-600');
                                        }
                                    }

                                    // Initialize colors on load
                                    document.addEventListener('DOMContentLoaded', function() {
                                        document.querySelectorAll('.dynamic-state-select').forEach(function(el) {
                                            updateSelectStateColor(el);
                                        });
                                    });
                                </script>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6">
                                </div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->

                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    @if (Auth::user()->can('Edit Pengaturan Aplikasi'))
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                    @endif
                                    <!--end::Button-->
                                </div>
                                <!--end::Action buttons-->
                            </form>
                            <!--end::Form-->
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
    $('.time').mask('00:00', {
        reverse: true
    });
    
    // Auto-format thousand separators for currency inputs
    document.querySelectorAll('.input-currency').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val !== '') {
                this.value = parseInt(val, 10).toLocaleString('id-ID');
            } else {
                this.value = '';
            }
        });
    });

    // Dynamically update class placeholders when school limit is changed
    document.querySelectorAll('.school-limit-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', updateClassPlaceholders);
    });
    document.querySelectorAll('.school-limit-input').forEach(function(input) {
        input.addEventListener('input', updateClassPlaceholders);
    });

    function updateClassPlaceholders() {
        document.querySelectorAll('.school-limit-toggle').forEach(function(toggle) {
            let schoolId = toggle.dataset.schoolId;
            let isActive = toggle.checked;
            let schoolInput = document.querySelector('.school-limit-input[data-school-id="'+schoolId+'"]');
            let schoolVal = schoolInput ? schoolInput.value : '';
            
            let placeholderText = 'Maksimal';
            if (isActive) {
                placeholderText = 'Mewarisi (' + (schoolVal || '0') + ')';
            }
            
            document.querySelectorAll('.class-limit-input[data-school-id="'+schoolId+'"]').forEach(function(classInput) {
                classInput.placeholder = placeholderText;
            });
        });
    }
</script>
@endpush