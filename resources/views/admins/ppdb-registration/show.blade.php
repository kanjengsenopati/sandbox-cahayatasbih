@extends('layouts.master', ['title' => 'Detail Data PPDB'])

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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Detail Data PPDB</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('user.index') }}">
                        <li class="breadcrumb-item text-muted">User</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Detail PPDB</li>
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
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Svg Icon | path: icons/duotune/communication/com005.svg-->
                                <span class="svg-icon svg-icon-1 me-2">
                                    {{-- add online icon admin --}}

                                </span>
                                <!--end::Svg Icon-->
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    Detail User</h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <div class="mb-5 hover-scroll-x">
                                <div class="d-grid">
                                    <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0 {{ request()->tab == 'status' || request()->tab == null ? 'active' : '' }}"
                                                data-bs-toggle="tab" href="#kt_tab_pane_4">
                                                Status Pendaftaran
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0 {{ request()->tab == 'info' ? 'active' : '' }}"
                                                data-bs-toggle="tab" href="#kt_tab_pane_1">
                                                Informasi PPDB
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0 {{ request()->tab == 'student' ? 'active' : '' }} "
                                                data-bs-toggle="tab" href="#kt_tab_pane_2">
                                                Data Peserta
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0 {{ request()->tab == 'parent' ? 'active' : '' }} "
                                                data-bs-toggle="tab" href="#kt_tab_pane_3">
                                                Data Orang Tua
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0 {{ request()->tab == 'document' ? 'active' : '' }} "
                                                data-bs-toggle="tab" href="#kt_tab_pane_6">
                                                Dokumen Pendukung
                                            </a>
                                        </li>
                                        {{-- <li class="nav-item">
                                            <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                                data-bs-toggle="tab" href="#kt_tab_pane_5">
                                                Reward Claim History
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade {{ request()->tab == 'status' || request()->tab == null ? 'active show' : '' }}"
                                    id="kt_tab_pane_4" role="tabpanel">
                                    {{-- add action to change status --}}
                                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                                        <!--begin::Card header-->
                                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                                            <!--begin::Card title-->
                                            <div class="card-title d-flex flex-column">
                                                <h1 class="text-dark fw-bolder fs-3 align-items-center">
                                                    {{ $ppdbRegistration->no_reg ?? 'Belum
                                                    diatur' }}
                                                </h1>
                                                <div class="mt-2">
                                                    <span class="text-muted fs-6 fw-bold mt-3">Nama Peserta</span>
                                                    <span class=" fs-6 fw-bold mt-3">{{
                                                        $ppdbRegistration->ppdbStudents?->first()?->name ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Card title-->
                                        <!--end::Card header-->
                                        <!--end::Card body-->
                                        <div class="card-body pt-5">
                                            {{-- add image vector approval svg --}}
                                            <div class="text-center">
                                                <img src="{{ asset('assets/media/svg/approval/approval.svg') }}" alt=""
                                                    class="w-50">
                                                <span class="text-muted fs-6 fw-bold d-block mt-3">Calon Peserta Didik
                                                    telah mendaftar, silahkan lakukan verifikasi data</span>
                                            </div>

                                            <form class="form"
                                                action="{{ route('ppdb-registration.update', $ppdbRegistration->id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="fv-row mb-6">
                                                    <!--begin::Label-->
                                                    <label class="fs-6 fw-bold form-label" for="name">
                                                        <span class="required">Status </span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                            data-bs-toggle="tooltip" title="Pilih Status PPDB"></i>
                                                    </label>
                                                    <!--end::Label-->
                                                    <!--begin::Input-->
                                                    <select name="status" class="form-select form-select-solid"
                                                        id="status" data-placeholder="Pilih Status">
                                                        <option value="PENDING" {{ @$ppdbRegistration->status ==
                                                            'PENDING' ? 'selected' : '' }}>
                                                            Menunggu Pembayaran
                                                        </option>
                                                        <option value="PAID" {{ @$ppdbRegistration->status == 'PAID' ?
                                                            'selected' : '' }}>
                                                            Pembayaran Diterima
                                                        </option>
                                                        <option value="REJECTED" {{ @$ppdbRegistration->status ==
                                                            'REJECTED' ? 'selected' : '' }}>
                                                            Tidak Lolos Seleksi
                                                        </option>
                                                        <option value="APPROVED" {{ @$ppdbRegistration->status ==
                                                            'APPROVED' ? 'selected' : '' }}>
                                                            Diterima
                                                        </option>
                                                    </select>
                                                    <!--end::Input-->
                                                </div>
                                                <div class="fv-row mb-6" id="note">
                                                    <!--begin::Label-->
                                                    <label class="fs-6 fw-bold form-label" for="note">
                                                        <span class="required">Keterangan </span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                            data-bs-toggle="tooltip" title="Masukkan Keterangan"></i>
                                                    </label>
                                                    <!--end::Label-->
                                                    <!--begin::Input-->
                                                    <textarea name="note" class="form-control form-control-solid"
                                                        rows="3" placeholder="Masukkan Keterangan"
                                                        required> {{ @$ppdbRegistration->note }}</textarea>
                                                    <!--end::Input-->
                                                </div>
                                                <!--begin::Separator-->
                                                <div class="separator mb-6"></div>
                                                <!--end::Separator-->
                                                <!--begin::Action buttons-->
                                                <div class="d-flex justify-content-end">
                                                    <!--begin::Button-->
                                                    <!--end::Button-->
                                                    <!--begin::Button-->
                                                    <button type="submit" data-kt-contacts-type="submit"
                                                        class="btn btn-sm btn-primary">
                                                        <span class="indicator-label">Simpan</span>
                                                        <span class="indicator-progress">Please wait...
                                                            <span
                                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                    </button>
                                                    <!--end::Button-->
                                                </div>
                                                <!--end::Action buttons-->
                                            </form>
                                        </div>
                                    </div>
                                    {{-- end change status --}}

                                </div>
                                <div class="tab-pane fade {{ request()->tab == 'info' ? 'active show' : '' }}"
                                    id="kt_tab_pane_1" role="tabpanel">
                                    <div class="d-flex align-items-start gap-5 px-4 pt-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                                            {{-- <img class="img_profile"
                                                src="{{ $user->avatar ? asset($user->avatar) : asset('/assets/media/avatars/default.png') }}"
                                                alt="Avatar User">
                                            <span class="badge badge-secondary"><img style="width: 16px"
                                                    src="{{ asset($user->level->badge) }}" alt="">
                                                Level {{ $user->level->name }}</span> --}}
                                        </div>
                                        <div class="ms-5 ps-3 w-100">
                                            <!-- <div class=""> -->
                                            <table class="profile table">
                                                <tr>
                                                    <td class="grey" width="20%">No. Pendaftaran</td>
                                                    <td class="pe-3"></td>
                                                    <td>{{ $ppdbRegistration->no_reg ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Biaya Pendaftaran</td>
                                                    <td></td>
                                                    <td>Rp. {{ number_format($ppdbRegistration->register_fee) ?? 0 }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Status Pembayaran</td>
                                                    <td></td>
                                                    <td>@if($ppdbRegistration->payment_status == 'PAID')
                                                        <span class="badge badge-success">Lunas</span>
                                                        @else
                                                        <span class="badge badge-danger">Belum Lunas</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Status Pendaftaran</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->translated_status ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Tanggal Pendaftaran</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->created_at->format('d F Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Akun Wali</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->user->name ?? 'Belum diatur' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">No HP Wali</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->user->phone ?? 'Belum diatur' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade {{ request()->tab == 'student' ? 'active show' : ''}}
                                " id="kt_tab_pane_2" role="tabpanel">
                                    <div class="d-flex align-items-start gap-5 px-4 pt-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                                            {{-- <img class="img_profile"
                                                src="{{ $user->avatar ? asset($user->avatar) : asset('/assets/media/avatars/default.png') }}"
                                                alt="Avatar User">
                                            <span class="badge badge-secondary"><img style="width: 16px"
                                                    src="{{ asset($user->level->badge) }}" alt="">
                                                Level {{ $user->level->name }}</span> --}}
                                        </div>
                                        <div class="ms-5 ps-3 w-100">
                                            <!-- <div class=""> -->
                                            <table class="profile table">
                                                <tr>
                                                    <td class="grey" width="20%">Nama Lengkap</td>
                                                    <td class="pe-3"></td>
                                                    <td>{{ $ppdbRegistration->ppdbStudents?->first()?->name ?? 'Belum
                                                        diatur' }}</td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">NISN</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbStudents?->first()?->nisn ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">NIK</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbStudents?->first()?->nik ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Asal Sekolah</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbStudents?->first()?->origin_school ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Tempat Lahir</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbStudents?->first()?->place_of_birth
                                                        ??
                                                        'Belum
                                                        diatur' }}</td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Tanggal Lahir</td>
                                                    <td></td>
                                                    <td>
                                                        {{ $ppdbRegistration->ppdbStudents?->first()?->date_of_birth ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Jenis Kelamin</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbStudents?->first()?->gender == 'L' ?
                                                        'Laki-laki' : 'Perempuan' ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Alamat</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbStudents?->first()?->address ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade {{ request()->tab == 'parent' ? 'active show' : ''}}"
                                    id="kt_tab_pane_3" role="tabpanel">
                                    <div class="d-flex align-items-start gap-5 px-4 pt-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                                            {{-- <img class="img_profile"
                                                src="{{ $user->avatar ? asset($user->avatar) : asset('/assets/media/avatars/default.png') }}"
                                                alt="Avatar User">
                                            <span class="badge badge-secondary"><img style="width: 16px"
                                                    src="{{ asset($user->level->badge) }}" alt="">
                                                Level {{ $user->level->name }}</span> --}}
                                        </div>
                                        <div class="ms-5 ps-3 w-100">
                                            <!-- <div class=""> -->
                                            <table class="profile table">
                                                <tr>
                                                    <td class="grey" width="20%">No. Kartu Keluarga</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbParents?->first()?->family_card_number
                                                        ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Nama Ayah</td>
                                                    <td class="pe-3"></td>
                                                    <td>{{ $ppdbRegistration->ppdbParents?->first()?->father_name ??
                                                        'Belum
                                                        diatur' }}</td>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="grey" width="20%">NIK Ayah</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbParents?->first()?->father_nik ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Status Ayah</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbParents?->first()?->father_status ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Pendidikan Ayah</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbParents?->first()?->father_education
                                                        ??
                                                        'Belum
                                                        diatur' }}</td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Pekerjaan Ayah</td>
                                                    <td></td>
                                                    <td>
                                                        {{ $ppdbRegistration->ppdbParents?->first()?->father_job ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Nama Ibu</td>
                                                    <td></td>
                                                    <td>
                                                        {{ $ppdbRegistration->ppdbParents?->first()?->mother_name ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">NIK Ibu</td>
                                                    <td></td>
                                                    <td>
                                                        {{ $ppdbRegistration->ppdbParents?->first()?->mother_nik ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Status Ibu</td>
                                                    <td></td>
                                                    <td>
                                                        {{ $ppdbRegistration->ppdbParents?->first()?->mother_status ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Pendidikan Ibu</td>
                                                    <td></td>
                                                    <td>
                                                        {{ $ppdbRegistration->ppdbParents?->first()?->mother_education
                                                        ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Pekerjaan Ibu</td>
                                                    <td></td>
                                                    <td>
                                                        {{ $ppdbRegistration->ppdbParents?->first()?->mother_job
                                                        ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Jenis Kartu Bantuan dari Pemerintah
                                                        yang dimiliki</td>
                                                    <td></td>
                                                    <td>{{
                                                        $ppdbRegistration->ppdbParents?->first()?->government_aid_card_type
                                                        ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Status Jamaah Pro Aktif Revolusi</td>
                                                    <td></td>
                                                    <td>
                                                        @if($ppdbRegistration->ppdbParents?->first()?->is_member == 1)
                                                        <span class="badge badge-success">Ya</span>
                                                        @elseif($ppdbRegistration->ppdbParents?->first()?->is_member ===
                                                        0)
                                                        <span class="badge badge-danger">Tidak</span>
                                                        @else
                                                        Belum diatur
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if($ppdbRegistration->ppdbParents?->first()?->is_member == 1)
                                                <tr>
                                                    <td class="grey" width="20%">Jamaah Cabang</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbParents?->first()?->mdti_branch ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">No Anggota</td>
                                                    <td></td>
                                                    <td>{{ $ppdbRegistration->ppdbParents?->first()?->member_number ??
                                                        'Belum
                                                        diatur' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="grey" width="20%">Foto KTA</td>
                                                    <td></td>
                                                    <td><img src="{{ asset($ppdbRegistration->ppdbParents?->first()?->photo_card
                                                        ?? '' ) }}" alt="" class="w-50">
                                                    </td>
                                                </tr>
                                                @endif

                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="kt_tab_pane_6" role="tabpanel">
                                    <div class="d-flex align-items-start gap-5 px-4 pt-5">
                                        <div class="ms-5 ps-3 w-100">
                                            <!-- <div class=""> -->
                                            <table class="profile table">
                                                <tr>
                                                    <td class="grey" width="20%">Scan Kartu Keluarga</td>
                                                    <td></td>
                                                    <td><img src="{{ asset($ppdbRegistration->ppdbDocument?->family_card_image ?? '' ) }}"
                                                            alt="" class="w-50">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class=" grey" width="20%">Scan Akta Kelahiran</td>
                                                    <td class="pe-3"></td>
                                                    <td><img src="{{ asset($ppdbRegistration->ppdbDocument?->birth_certificate_image ?? '' ) }}"
                                                            alt="" class="w-50">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class=" grey" width="20%">Scan SKL / Raport</td>
                                                    <td class="pe-3"></td>
                                                    <td><img src="{{ asset($ppdbRegistration->ppdbDocument?->raport_image ?? '' ) }}"
                                                            alt="" class="w-50">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class=" grey" width="20%">Scan KTP Ayah</td>
                                                    <td class="pe-3"></td>
                                                    <td><img src="{{ asset($ppdbRegistration->ppdbDocument?->father_identity_image ?? '' ) }}"
                                                            alt="" class="w-50">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class=" grey" width="20%">Scan KTP Ibu</td>
                                                    <td class="pe-3"></td>
                                                    <td><img src="{{ asset($ppdbRegistration->ppdbDocument?->mother_identity_image ?? '' ) }}"
                                                            alt="" class="w-50">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-reward" class="table table-striped border rounded gy-5 gs-7">
                                            <thead>
                                                <tr class="fw-bolder fs-6 text-gray-800 px-7">
                                                    <th width="3%">No</th>
                                                    <th class="nowrap">Reward</th>
                                                    <th class="nowrap">Value</th>
                                                    <th>Claimed At</th>
                                                    <th>Expired At</th>
                                                    <th>Used At</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div> --}}
                            </div>
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
{{-- @push('js')
<script>
    $(document).ready(() => {
            var table = $('#table-run').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $user->id) }}",
                    type: 'GET',
                    data: {
                        type: 'history'
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -1,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'range',
                        name: 'range'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'step',
                        name: 'step'
                    },
                    {
                        data: 'calory',
                        name: 'calory',
                    },
                    {
                        data: 'pace',
                        name: 'pace',
                    },
                    {
                        data: 'start_at',
                        name: 'start_at',
                    },
                    {
                        data: 'finish_at',
                        name: 'finish_at',
                    },
                ]
            });

            var tableChallenge = $('#table-challenge').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $user->id) }}",
                    type: 'GET',
                    data: {
                        type: 'challenge'
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -1,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'challenge',
                        name: 'challenge'
                    },
                    {
                        data: 'range',
                        name: 'range'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'step',
                        name: 'step'
                    },
                    {
                        data: 'calory',
                        name: 'calory',
                    },
                    {
                        data: 'pace',
                        name: 'pace',
                    },
                    {
                        data: 'start_at',
                        name: 'start_at',
                    },
                    {
                        data: 'finish_at',
                        name: 'finish_at',
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                    {
                        data: 'failed_note',
                        name: 'failed_note',
                    }
                ]
            });

            var tableReward = $('#table-poin').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $user->id) }}",
                    type: 'GET',
                    data: {
                        type: 'point'
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -1,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },

                    {
                        data: 'note',
                        name: 'note',
                    },
                    {
                        data: 'point',
                        name: 'point'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        responsivePriority: -1,
                    },
                ]
            });

            var tableReward = $('#table-xp').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $user->id) }}",
                    type: 'GET',
                    data: {
                        type: 'xp'
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -1,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },  
                    {
                        data: 'xp',
                        name: 'xp',
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        responsivePriority: -1,
                    },
                ]
            });

            var tableReward = $('#table-reward').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('user.show', $user->id) }}",
                    type: 'GET',
                    data: {
                        type: 'reward'
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -1,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'reward',
                        name: 'reward',
                    },
                    {
                        data: 'value',
                        name: 'value'
                    },
                    {
                        data: 'claimed_at',
                        name: 'claimed_at',
                        responsivePriority: -1,
                    },
                    {
                        data: 'expired_at',
                        name: 'expired_at',
                        responsivePriority: -1,
                    },
                    {
                        data: 'used_at',
                        name: 'used_at',
                        responsivePriority: -1,
                    },
                    {
                        data: 'status',
                        name: 'status',
                        responsivePriority: -1,
                    },
                ]

            });
        })
</script>
@endpush --}}