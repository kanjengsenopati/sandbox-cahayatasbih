@extends('layouts.master', ['title' => 'Data Pendafataran PPDB'])
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">PPDB</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('wali.ppdb.index') }}">
                        <li class="breadcrumb-item text-muted">List PPDB</li>
                    </a>
                    <!--end::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('wali.ppdb.create') ? 'Daftar PPDB' : 'Edit PPDB' }}
                    </li>
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
                <form action="{{ route('wali.ppdb.store') }}" method="POST" enctype="multipart/form-data">
                    <div class="col-xl-12">
                        <!--begin::Contacts-->
                        @csrf
                        <input type="hidden" name="ppdb_id" value="{{ $ppdb->id }}">
                        <div class=" card card-flush h-lg-100" id="kt_contacts_main">
                            <!--begin::Card header-->
                            <div class="card-header pt-7" id="kt_chat_contacts_header">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex flex-column">
                                            <h2 class="fw-bolder fs-2hx">{{ $ppdb->name }}</h2>
                                            <span class="text-muted fw-bold fs-6">Jalur {{ $ppdb->ppdbType->name
                                                }} - {{
                                                $ppdb->academicYear->name }} - {{ $ppdb->school->name }}</span>
                                        </div>
                                        <!--end::Card title-->
                                    </div>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class=" card mb-5 mb-xl-10">
                                <!--begin::Card header-->
                                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                    data-bs-target="#kt_account_profile_details" aria-expanded="true"
                                    aria-controls="kt_account_profile_details">
                                    <!--begin::Card title-->
                                    <div class="card-title m-0">
                                        <h3 class="fw-bolder m-0">DATA SISWA/SISWI</h3>
                                    </div>
                                    <!--end::Card title-->
                                </div>
                                <!--begin::Card header-->
                                <!--begin::Content-->
                                <div id="kt_account_settings_profile_details" class="collapse show">
                                    <!--begin::Form-->
                                    <!--begin::Card body-->
                                    <div class="card-body border-top p-9">
                                        <x-alert.alert-validation />
                                        <!--begin::Input group-->
                                        <!--begin::Input group-->
                                        <div class="row mb-6">
                                            <div class="col-6">
                                                <!--begin::Label-->
                                                <label class="fs-6 fw-bold form-label" for="name">
                                                    <span class="required">Nama</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                        data-bs-toggle="tooltip" title="Masukkan Nama"></i>
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="text" class="form-control form-control-solid" id="name"
                                                    name="name" placeholder="Masukkan Nama Lengkap (Sesuai Ijazah)"
                                                    value="{{ old('name') }}" />
                                                <!--end::Input-->
                                            </div>
                                            <div class="col-6">
                                                <!--begin::Label-->
                                                <label class="fs-6 fw-bold form-label" for="gender">
                                                    <span class="required">Jenis Kelamin</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                        data-bs-toggle="tooltip" title="Pilih Jenis Kelamin"></i>
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <select class="form-control form-control-solid" id="gender"
                                                    name="gender" required>
                                                    <option value="">Pilih Jenis Kelamin</option>
                                                    <option value="L">Laki-laki</option>
                                                    <option value="P">Perempuan</option>
                                                </select>
                                                <!--end::Input-->
                                            </div>

                                        </div>

                                        <div class="row mb-6">
                                            <div class="col-6">
                                                <!--begin::Label-->
                                                <label class="fs-6 fw-bold form-label" for="place_of_birth">
                                                    <span class="required">Tempat Lahir</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                        data-bs-toggle="tooltip" title="Masukkan Tempat Lahir"></i>
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="text" class="form-control form-control-solid"
                                                    id="place_of_birth" name="place_of_birth"
                                                    placeholder="Masukkan Tempat Lahir"
                                                    value="{{ old('place_of_birth') }}" />
                                                <!--end::Input-->
                                            </div>
                                            <div class="col-6">
                                                <!--begin::Label-->
                                                <label class="fs-6 fw-bold form-label" for="date_of_birth">
                                                    <span class="required">Tanggal Lahir</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                        data-bs-toggle="tooltip" title="Masukkan Tanggal Lahir"></i>
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="date" class="form-control form-control-solid"
                                                    id="date_of_birth" name="date_of_birth"
                                                    placeholder="Masukkan Tanggal Lahir"
                                                    value="{{ old('date_of_birth') }}" />
                                                <!--end::Input-->
                                            </div>
                                        </div>

                                        <div class="row mb-6">
                                            <div class="col-6">
                                                <!--begin::Label-->
                                                <label class="fs-6 fw-bold form-label" for="nik">
                                                    <span class="required">NIK (Nomor Induk
                                                        Kependudukan)</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                        data-bs-toggle="tooltip" title="Masukkan NIK"></i>
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="number" class="form-control form-control-solid" id="nik"
                                                    name="nik" placeholder="Masukkan NIK" value="{{ old('nik') }}" />
                                                <!--end::Input-->
                                            </div>
                                            <div class="col-6">
                                                <!--begin::Label-->
                                                <label class="fs-6 fw-bold form-label" for="name">
                                                    <span class="required">NISN (Nomor Induk Siswa
                                                        Nasional)</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                        data-bs-toggle="tooltip"
                                                        title="Lihat di buku Raport atau SKHU sementara"></i>
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="text" class="form-control form-control-solid" id="nisn"
                                                    name="nisn" placeholder="Lihat di buku Raport atau SKHU sementara"
                                                    value="{{ old('nisn') }}" />
                                                <!--end::Input-->
                                            </div>
                                        </div>

                                        <div class="fw-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label" for="address">
                                                <span class="required">Alamat Lengkap (Sesuai dengan
                                                    alamat tinggal
                                                    sekarang atau sesuai alamat di Kartu
                                                    Keluarga)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Masukkan Alamat Lengkap dengan RT/RW dan Kode Pos"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <textarea class="form-control form-control-solid" id="address" rows="3"
                                                name="address"
                                                placeholder="Masukkan Alamat Lengkap dengan RT/RW dan Kode Pos dan Nama Jalan"
                                                value="{{ old('address') }}"></textarea>
                                            <!--end::Input-->
                                        </div>
                                        <div class="fw-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label" for="origin_school">
                                                <span class="required">Asal Sekolah</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Masukkan Asal Sekolah"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input name="origin_school" type="text"
                                                class="form-control form-control-solid"
                                                placeholder="Masukkan Asal Sekolah" value="{{ old('origin_school') }}"
                                                required />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                <!--end::Content-->
                            </div>
                        </div>


                        <!--end::Contacts-->
                    </div>
                    <!--begin::Sign-in Method-->
                    <div class="card mb-5 mb-xl-10">
                        <!--begin::Card header-->
                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                            data-bs-target="#kt_account_signin_method">
                            <div class="card-title m-0">
                                <h3 class="fw-bolder m-0">DATA ORANG TUA/WALI</h3>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Content-->
                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <!--begin::Card body-->
                            <div class="card-body border-top p-9">
                                <!--begin::Email Address-->
                                <div class="fw-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="family_card_number">
                                        <span class="required">Nomor Kartu Keluarga</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Nomor Kartu Keluarga"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="number" class="form-control form-control-solid" id="family_card_number"
                                        name="family_card_number" placeholder="Masukkan Nomor Kartu Keluarga"
                                        value="{{ old('family_card_number') }}" />
                                    <!--end::Input-->
                                </div>
                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="father_name">
                                            <span class="required">Nama Ayah</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nama Ayah"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="number" class="form-control form-control-solid" id="father_name"
                                            name="father_name" placeholder="Masukkan Nama Ayah"
                                            value="{{ old('father_name') }}" />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="father_nik">
                                            <span class="required">NIK Ayah</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan NIK Ayah"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="number" class="form-control form-control-solid" id="father_nik"
                                            name="father_nik" placeholder="Masukkan NIK Ayah"
                                            value=" {{ old('father_nik') }}" />
                                        <!--end::Input-->
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="father_status">
                                            <span class="required">Status Ayah</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Status Ayah"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <select class="form-control form-control-solid" id="father_status"
                                            name="father_status" required>
                                            <option value="">Pilih Status Ayah</option>
                                            <option value="Hidup">Hidup</option>
                                            <option value="Meninggal">Meninggal</option>
                                            <option value="Tidak Diketahui">Tidak Diketahui</option>
                                        </select>
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="father_education">
                                            <span class="required">Pendidikan Ayah</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Pendidikan Ayah"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <select name="father_education" id="father_education"
                                            class="form-control form-control-solid" required>
                                            <option value="">Pilih Pendidikan Ayah</option>
                                            <option value="Tidak Sekolah">Tidak Sekolah</option>
                                            <option value="SD">SD</option>
                                            <option value="SMP">SMP</option>
                                            <option value="SMA">SMA</option>
                                            <option value="D1">D1</option>
                                            <option value="D2">D2</option>
                                            <option value="D3">D3</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                        <!--end::Input-->
                                    </div>
                                </div>
                                <div class="fw-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="father_job">
                                        <span class="required">Pekerjaan Ayah</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Pekerjaan Ayah"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="father_job" id="father_job" class="form-control form-control-solid"
                                        required>
                                        <option value="">Pilih Pekerjaan Ayah</option>
                                        <option value="Tidak Bekerja">Tidak Bekerja</option>
                                        <option value="Petani">Petani</option>
                                        <option value="Nelayan">Nelayan</option>
                                        <option value="Pedagang">Pedagang</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/POLRI">TNI/POLRI</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="mother_name">
                                            <span class="required">Nama Ibu</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nama Ibu"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="number" class="form-control form-control-solid" id="mother_name"
                                            name="mother_name" placeholder="Masukkan Nama Ibu"
                                            value="{{ old('mother_name') }}" />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="mother_nik">
                                            <span class="required">NIK Ibu</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan NIK Ibu"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="number" class="form-control form-control-solid" id="mother_nik"
                                            name="mother_nik" placeholder="Masukkan NIK Ibu"
                                            value=" {{ old('mother_nik') }}" />
                                        <!--end::Input-->
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="mother_status">
                                            <span class="required">Status Ibu</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih Status Ibu"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <select class="form-control form-control-solid" id="mother_status"
                                            name="mother_status" required>
                                            <option value="">Pilih Status Ibu</option>
                                            <option value="Hidup">Hidup</option>
                                            <option value="Meninggal">Meninggal</option>
                                            <option value="Tidak Diketahui">Tidak Diketahui</option>
                                        </select>
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="mother_education">
                                            <span class="required">Pendidikan Ibu</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Pendidikan Ibu"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <select name="mother_education" id="mother_education"
                                            class="form-control form-control-solid" required>
                                            <option value="">Pilih Pendidikan Ibu</option>
                                            <option value="Tidak Sekolah">Tidak Sekolah</option>
                                            <option value="SD">SD</option>
                                            <option value="SMP">SMP</option>
                                            <option value="SMA">SMA</option>
                                            <option value="D1">D1</option>
                                            <option value="D2">D2</option>
                                            <option value="D3">D3</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                        <!--end::Input-->
                                    </div>
                                </div>
                                <div class="fw-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="mother_job">
                                        <span class="required">Pekerjaan Ibu</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Pekerjaan Ibu"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="mother_job" id="mother_job" class="form-control form-control-solid"
                                        required>
                                        <option value="">Pilih Pekerjaan Ibu</option>
                                        <option value="Tidak Bekerja">Tidak Bekerja</option>
                                        <option value="Petani">Petani</option>
                                        <option value="Nelayan">Nelayan</option>
                                        <option value="Pedagang">Pedagang</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/POLRI">TNI/POLRI</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <!--end::Input-->
                                </div>

                                <div class="fw-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="government_aid_card_type">
                                        <span class="required">Jenis Kartu Bantuan dari Pemerintah yang
                                            dimiliki</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Jenis Kartu Bantuan dari Pemerintah yang dimiliki"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="government_aid_card_type" id="government_aid_card_type"
                                        class="form-control form-control-solid" required>
                                        <option value="">Pilih Jenis Kartu</option>
                                        <option value="KIP">KIP</option>
                                        <option value="PKH">PKH</option>
                                        <option value="KKS/KPS">KKS/KPS</option>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                    </select>
                                    <!--end::Input-->
                                </div>

                                @if ($ppdb->ppdbType->type == 'JAMAAH')
                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="mdti_branch">
                                            <span class="required">Jamaah MDTI Cabang</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Cabang MDTI"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" class="form-control form-control-solid" id="mdti_branch"
                                            name="mdti_branch" placeholder="Masukkan Cabang MDTI"
                                            value="{{ old('mdti_branch') }}" />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="member_number">
                                            <span class="required">Nomor KTA</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Nomor KTA"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" class="form-control form-control-solid" id="member_number"
                                            name="member_number" placeholder="Masukkan Nomor KTA"
                                            value=" {{ old('member_number') }}" />
                                        <!--end::Input-->
                                    </div>

                                </div>

                                <div class="fv-row mb-6">
                                    <x-form.image-upload
                                        label="Upload foto KTA jama'ah (bagi yang tidak punya KTA bisa upload kartu jariyah SKG anda)"
                                        name="photo_card" :value="@$ppdbRegister->ppdbParent->photo_card ?? null" />
                                </div>
                                @endif
                                <!--end::Email Address-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-6"></div>
                                <!--end::Separator-->

                            </div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Sign-in Method-->

                    <!--begin::Sign-in Method-->
                    <div class="card mb-5 mb-xl-10">
                        <!--begin::Card header-->
                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                            data-bs-target="#kt_account_signin_method">
                            <div class="card-title m-0">
                                <h3 class="fw-bolder m-0">DOKUMEN PENDUKUNG</h3>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Content-->
                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <!--begin::Card body-->
                            <div class="card-body border-top p-9">
                                <!--begin::Email Address-->
                                <div class="fw-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="family_card_image">
                                        <span class="required">Upload Kartu Keluarga</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Upload Kartu Keluarga Yang Sudah Di Scan"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="file" class="form-control form-control-solid" id="family_card_image"
                                        name="family_card_image" placeholder="Upload Kartu Keluarga"
                                        value="{{ old('family_card_image') }}" required />
                                    <!--end::Input-->
                                </div>
                                <div class="row mb-6">

                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="father_identity_image">
                                            <span class="required">Upload KTP Ayah</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Upload KTP Ayah Yang Sudah Di Scan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="file" class="form-control form-control-solid"
                                            id="father_identity_image" name="father_identity_image"
                                            placeholder="Upload KTP Ayah" value=" {{ old('father_identity_image') }}"
                                            required />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="mother_identity_image">
                                            <span class="required">Upload KTP Ibu</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Upload KTP Ibu Yang Sudah Di Scan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="file" class="form-control form-control-solid"
                                            id="mother_identity_image" name="mother_identity_image"
                                            placeholder="Upload KTP Ibu" value="{{ old('mother_identity_image') }}"
                                            required />
                                        <!--end::Input-->
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="birth_certificate_image">
                                            <span class="required">Upload Akta Kelahiran</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Upload Akta Kelahiran Yang Sudah Di Scan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="file" class="form-control form-control-solid"
                                            id="birth_certificate_image" name="birth_certificate_image"
                                            placeholder="Upload Akta Kelahiran"
                                            value="{{ old('birth_certificate_image') }}" required />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="raport_image">
                                            <span class="required">Upload SKL/Raport</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Upload SKL/Raport Yang Sudah Di Scan"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="file" class="form-control form-control-solid" id="raport_image"
                                            name="raport_image" placeholder="Upload SKL/Raport"
                                            value="{{ old('raport_image') }}" required />
                                        <!--end::Input-->
                                    </div>
                                </div>

                                <!--end::Email Address-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-6"></div>
                                <!--end::Separator-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('wali.ppdb.index') }}">
                                        <button type="button" data-kt-contacts-type="cancel"
                                            class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                    <!--end::Button-->
                                </div>
                            </div>
                            <!--end::Card body-->
                        </div>
                </form>
                <!--end::Content-->
            </div>
            <!--end::Sign-in Method-->
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