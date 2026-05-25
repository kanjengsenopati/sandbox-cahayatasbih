@extends('layouts.master', ['title' => 'Desain & Cetak Kartu Santri'])
@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Desain & Cetak Kartu</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <a class="breadcrumb-item" href="{{ route('student-card-setting.index') }}">
                        <li class="breadcrumb-item text-muted">Pengaturan</li>
                    </a>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-dark">
                        <span class="text-muted fw-bolder fs-7">Desain & Cetak Kartu Santri</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <style>
                text-h1, .text-h1 { display: block; font-size: 22px; font-weight: 700; color: #0f172a; } /* Slate-900 */
                text-h2, .text-h2 { display: block; font-size: 16px; font-weight: 600; color: #1e293b; } /* Slate-800 */
                text-amount, .text-amount { display: inline-block; font-size: 18px; font-weight: 700; color: #059669; } /* Emerald-600 */
                text-label, .text-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; } /* Slate-400 */
                text-body, .text-body { display: block; font-size: 14px; font-weight: 500; color: #475569; } /* Slate-600 */
                text-caption, .text-caption { display: block; font-size: 12px; font-style: italic; color: #94a3b8; } /* Slate-400 */
            </style>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!--begin::Tabs Navigation-->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6" id="cardTabs">
                <li class="nav-item">
                    <a class="nav-link active fw-bolder" data-bs-toggle="tab" href="#tab_desain">
                        <i class="fa-solid fa-palette me-2"></i>Desain Kartu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bolder" data-bs-toggle="tab" href="#tab_cetak">
                        <i class="fa-solid fa-print me-2"></i>Cetak Kartu
                    </a>
                </li>
            </ul>

            <!--begin::Tab Content-->
            <div class="tab-content" id="cardTabContent">

                {{-- ===================== TAB 1: DESAIN KARTU ===================== --}}
                <div class="tab-pane fade show active" id="tab_desain" role="tabpanel">
                    <form action="{{ route('student-card-setting.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-5">

                            {{-- Left Column: LIVE PREVIEW --}}
                            <div class="col-lg-6">
                                <div class="card card-flush border-0 shadow-[0_8px_30px_rgba(0,0,0,0.04)]" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); border: none;">
                                    <div class="card-header border-0 pb-0">
                                        <h3 class="card-title fw-bolder">Live Preview</h3>
                                    </div>
                                    <div class="card-body d-flex justify-content-center align-items-center" style="background:#e8e8e8; min-height:300px;">
                                        <div id="cardPreviewWrapper" style="width:342px; height:216px; position:relative; overflow:hidden; border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.15);">
                                            {{-- Background --}}
                                            <div id="prevBg" style="position:absolute;inset:0;background-size:cover;background-position:center;
                                                @if($background) background-image:url('{{ asset($background) }}'); @else background:linear-gradient(135deg,#1a4731,#10b981); @endif
                                            "></div>

                                            {{-- Logo --}}
                                            <img id="prevLogo" src="{{ asset('assets/media/logos/logo-full.png') }}"
                                                style="position:absolute;top:{{ ($layout['logo']['top'] ?? 5) * 4 }}px;left:{{ ($layout['logo']['left'] ?? 5) * 4 }}px;width:{{ ($layout['logo']['width'] ?? 25) * 4 }}px;height:{{ ($layout['logo']['height'] ?? 8) * 4 }}px;object-fit:contain;
                                                {{ ($layout['logo']['show'] ?? true) ? '' : 'display:none;' }}"
                                            />

                                            {{-- Title --}}
                                            <div id="prevTitle" style="position:absolute;
                                                top:{{ ($layout['title']['top'] ?? 5) * 4 }}px;
                                                left:{{ ($layout['title']['left'] ?? 45) * 4 }}px;
                                                width:{{ (86 - ($layout['title']['left'] ?? 45)) * 4 }}px;
                                                color:{{ $layout['title']['color'] ?? '#FFFF00' }};
                                                font-size:{{ ($layout['title']['font_size'] ?? 12) * 1.2 }}px;
                                                font-weight:{{ $layout['title']['font_weight'] ?? 'bold' }};
                                                text-align:{{ $layout['title']['text_align'] ?? 'right' }};
                                                {{ ($layout['title']['show'] ?? true) ? '' : 'display:none;' }}
                                            ">{{ $layout['title']['text'] ?? 'Kartu Santri' }}</div>

                                            {{-- Subtitle --}}
                                            <div id="prevSubtitle" style="position:absolute;
                                                top:{{ ($layout['subtitle']['top'] ?? 10) * 4 }}px;
                                                left:{{ ($layout['subtitle']['left'] ?? 45) * 4 }}px;
                                                width:{{ (86 - ($layout['subtitle']['left'] ?? 45)) * 4 }}px;
                                                color:{{ $layout['subtitle']['color'] ?? '#FFFFFF' }};
                                                font-size:{{ ($layout['subtitle']['font_size'] ?? 10) * 1.2 }}px;
                                                font-weight:{{ $layout['subtitle']['font_weight'] ?? 'bold' }};
                                                text-align:{{ $layout['subtitle']['text_align'] ?? 'right' }};
                                                {{ ($layout['subtitle']['show'] ?? true) ? '' : 'display:none;' }}
                                            ">{{ $layout['subtitle']['text'] ?? 'PPTQ Cahaya Tasbih' }}</div>

                                            {{-- Photo placeholder --}}
                                            <div id="prevPhoto" style="position:absolute;
                                                top:{{ ($layout['photo']['top'] ?? 18) * 4 }}px;
                                                left:{{ ($layout['photo']['left'] ?? 5) * 4 }}px;
                                                width:{{ ($layout['photo']['width'] ?? 18) * 4 }}px;
                                                height:{{ ($layout['photo']['height'] ?? 24) * 4 }}px;
                                                border-radius:{{ ($layout['photo']['border_radius'] ?? 2) * 4 }}px;
                                                background:#fff;opacity:0.85;
                                                display:flex;align-items:center;justify-content:center;
                                                {{ ($layout['photo']['show'] ?? false) ? '' : 'display:none;' }}
                                            "><i class="fa fa-user" style="font-size:24px;color:#ccc;"></i></div>

                                            {{-- Name --}}
                                            <div id="prevName" style="position:absolute;
                                                top:{{ ($layout['name']['top'] ?? 20) * 4 }}px;
                                                left:{{ ($layout['name']['left'] ?? 25) * 4 }}px;
                                                color:{{ $layout['name']['color'] ?? '#FFFFFF' }};
                                                font-size:{{ ($layout['name']['font_size'] ?? 12) * 1.2 }}px;
                                                font-weight:{{ $layout['name']['font_weight'] ?? 'bold' }};
                                                {{ ($layout['name']['show'] ?? true) ? '' : 'display:none;' }}
                                            ">Ahmad Santri</div>

                                            {{-- NIS --}}
                                            <div id="prevNis" style="position:absolute;
                                                top:{{ ($layout['nis']['top'] ?? 27) * 4 }}px;
                                                left:{{ ($layout['nis']['left'] ?? 25) * 4 }}px;
                                                color:{{ $layout['nis']['color'] ?? '#FFFFFF' }};
                                                font-size:{{ ($layout['nis']['font_size'] ?? 14) * 1.2 }}px;
                                                font-weight:{{ $layout['nis']['font_weight'] ?? 'bold' }};
                                                letter-spacing:2px;
                                                {{ ($layout['nis']['show'] ?? true) ? '' : 'display:none;' }}
                                            ">2024001</div>

                                            {{-- Classroom --}}
                                            <div id="prevClassroom" style="position:absolute;
                                                top:{{ ($layout['classroom']['top'] ?? 35) * 4 }}px;
                                                left:{{ ($layout['classroom']['left'] ?? 25) * 4 }}px;
                                                color:{{ $layout['classroom']['color'] ?? '#FFFFFF' }};
                                                font-size:{{ ($layout['classroom']['font_size'] ?? 9) * 1.2 }}px;
                                                font-weight:{{ $layout['classroom']['font_weight'] ?? 'bold' }};
                                                {{ ($layout['classroom']['show'] ?? true) ? '' : 'display:none;' }}
                                            ">Kelas 7A</div>

                                            {{-- School --}}
                                            <div id="prevSchool" style="position:absolute;
                                                top:{{ ($layout['school']['top'] ?? 40) * 4 }}px;
                                                left:{{ ($layout['school']['left'] ?? 25) * 4 }}px;
                                                color:{{ $layout['school']['color'] ?? '#FFFFFF' }};
                                                font-size:{{ ($layout['school']['font_size'] ?? 9) * 1.2 }}px;
                                                font-weight:{{ $layout['school']['font_weight'] ?? 'bold' }};
                                                {{ ($layout['school']['show'] ?? true) ? '' : 'display:none;' }}
                                            ">SMP Cahaya Tasbih</div>

                                            {{-- Code (barcode placeholder) --}}
                                            <div id="prevCode" style="position:absolute;
                                                top:{{ ($layout['code']['top'] ?? 42) * 4 }}px;
                                                left:{{ ($layout['code']['left'] ?? 55) * 4 }}px;
                                                width:{{ ($layout['code']['width'] ?? 26) * 4 }}px;
                                                height:{{ ($layout['code']['height'] ?? 8) * 4 }}px;
                                                background:#fff;border-radius:4px;padding:3px;
                                                display:flex;align-items:center;justify-content:center;
                                                {{ ($layout['code']['show'] ?? true) ? '' : 'display:none;' }}
                                            ">
                                                <span style="font-size:9px;font-family:monospace;color:#333;" id="prevCodeLabel">
                                                    {{ ($layout['code']['type'] ?? 'barcode') === 'qrcode' ? '▣ QR' : '||||| BARCODE |||||' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <small class="text-muted">Preview diskalakan 4x dari ukuran asli (85.6mm × 53.98mm)</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column: CONFIGURATION PANEL --}}
                            <div class="col-lg-6">
                                <div class="card card-flush border-0 shadow-[0_8px_30px_rgba(0,0,0,0.04)]" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); border: none;">
                                    <div class="card-header border-0 pb-0">
                                        <h3 class="card-title fw-bolder">Konfigurasi Elemen</h3>
                                    </div>
                                    <div class="card-body pt-2" style="max-height:600px;overflow-y:auto;">

                                        {{-- Background Image Upload --}}
                                        <div class="mb-5 p-4 bg-light-primary rounded">
                                            <label class="form-label fw-bold">Gambar Latar Belakang Kartu</label>
                                            @if($background)
                                                <div class="mb-3">
                                                    <img src="{{ asset($background) }}" class="rounded" style="max-width:200px;height:auto;" />
                                                </div>
                                            @endif
                                            <input type="file" name="student_card_image" class="form-control form-control-sm" accept="image/*" id="bgUpload" />
                                            <small class="text-muted">Format: JPG, PNG, WebP. Maks 2MB. Rasio ideal: 85.6 × 54mm</small>
                                        </div>

                                        {{-- Accordion Panels --}}
                                        <div class="accordion accordion-icon-toggle" id="layoutAccordion">

                                            @php
                                                $elements = [
                                                    'logo' => ['label' => 'Logo Lembaga', 'icon' => 'fa-image', 'fields' => ['show','top','left','width','height']],
                                                    'title' => ['label' => 'Judul Kartu', 'icon' => 'fa-heading', 'fields' => ['show','text','color','font_size','top','left','text_align','font_weight']],
                                                    'subtitle' => ['label' => 'Subtitle / Nama Lembaga', 'icon' => 'fa-font', 'fields' => ['show','text','color','font_size','top','left','text_align','font_weight']],
                                                    'photo' => ['label' => 'Foto Santri', 'icon' => 'fa-user-circle', 'fields' => ['show','top','left','width','height','border_radius']],
                                                    'name' => ['label' => 'Nama Santri', 'icon' => 'fa-id-card', 'fields' => ['show','color','font_size','top','left','font_weight']],
                                                    'nis' => ['label' => 'NIS', 'icon' => 'fa-hashtag', 'fields' => ['show','color','font_size','top','left','font_weight']],
                                                    'classroom' => ['label' => 'Kelas', 'icon' => 'fa-school', 'fields' => ['show','color','font_size','top','left','font_weight']],
                                                    'school' => ['label' => 'Sekolah / UPT', 'icon' => 'fa-building', 'fields' => ['show','color','font_size','top','left','font_weight']],
                                                    'code' => ['label' => 'Barcode / QR Code', 'icon' => 'fa-barcode', 'fields' => ['show','type','top','left','width','height']],
                                                ];
                                            @endphp

                                            @foreach($elements as $key => $el)
                                            <div class="mb-3">
                                                <div class="accordion-header py-3 d-flex align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#acc_{{ $key }}">
                                                    <span class="accordion-icon"><i class="fa-solid fa-angle-right fs-5"></i></span>
                                                    <h4 class="fw-bold mb-0 ms-3 fs-6">
                                                        <i class="fa-solid {{ $el['icon'] }} me-2 text-primary"></i>{{ $el['label'] }}
                                                    </h4>
                                                    <div class="form-check form-switch ms-auto me-3">
                                                        <input class="form-check-input elem-show-toggle" type="checkbox" name="layout[{{ $key }}][show]" value="1"
                                                            data-element="{{ $key }}"
                                                            {{ ($layout[$key]['show'] ?? ($key === 'photo' ? false : true)) ? 'checked' : '' }} />
                                                    </div>
                                                </div>
                                                <div id="acc_{{ $key }}" class="collapse" data-bs-parent="#layoutAccordion">
                                                    <div class="p-4 bg-light rounded">
                                                        <div class="row g-3">
                                                            @if(in_array('text', $el['fields']))
                                                            <div class="col-12">
                                                                <label class="form-label form-label-sm">Teks</label>
                                                                <input type="text" class="form-control form-control-sm live-input" name="layout[{{ $key }}][text]"
                                                                    value="{{ $layout[$key]['text'] ?? '' }}" data-element="{{ $key }}" data-prop="text" />
                                                            </div>
                                                            @endif
                                                            @if(in_array('top', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Top (mm)</label>
                                                                <input type="number" step="0.5" class="form-control form-control-sm live-input" name="layout[{{ $key }}][top]"
                                                                    value="{{ $layout[$key]['top'] ?? 0 }}" data-element="{{ $key }}" data-prop="top" />
                                                            </div>
                                                            @endif
                                                            @if(in_array('left', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Left (mm)</label>
                                                                <input type="number" step="0.5" class="form-control form-control-sm live-input" name="layout[{{ $key }}][left]"
                                                                    value="{{ $layout[$key]['left'] ?? 0 }}" data-element="{{ $key }}" data-prop="left" />
                                                            </div>
                                                            @endif
                                                            @if(in_array('width', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Width (mm)</label>
                                                                <input type="number" step="0.5" class="form-control form-control-sm live-input" name="layout[{{ $key }}][width]"
                                                                    value="{{ $layout[$key]['width'] ?? 20 }}" data-element="{{ $key }}" data-prop="width" />
                                                            </div>
                                                            @endif
                                                            @if(in_array('height', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Height (mm)</label>
                                                                <input type="number" step="0.5" class="form-control form-control-sm live-input" name="layout[{{ $key }}][height]"
                                                                    value="{{ $layout[$key]['height'] ?? 10 }}" data-element="{{ $key }}" data-prop="height" />
                                                            </div>
                                                            @endif
                                                            @if(in_array('color', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Warna</label>
                                                                <input type="color" class="form-control form-control-sm form-control-color live-input" name="layout[{{ $key }}][color]"
                                                                    value="{{ $layout[$key]['color'] ?? '#FFFFFF' }}" data-element="{{ $key }}" data-prop="color" />
                                                            </div>
                                                            @endif
                                                            @if(in_array('font_size', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Font Size (pt)</label>
                                                                <input type="number" min="6" max="30" class="form-control form-control-sm live-input" name="layout[{{ $key }}][font_size]"
                                                                    value="{{ $layout[$key]['font_size'] ?? 10 }}" data-element="{{ $key }}" data-prop="font_size" />
                                                            </div>
                                                            @endif
                                                            @if(in_array('font_weight', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Font Weight</label>
                                                                <select class="form-select form-select-sm live-input" name="layout[{{ $key }}][font_weight]" data-element="{{ $key }}" data-prop="font_weight">
                                                                    <option value="normal" {{ ($layout[$key]['font_weight'] ?? 'bold') === 'normal' ? 'selected' : '' }}>Normal</option>
                                                                    <option value="bold" {{ ($layout[$key]['font_weight'] ?? 'bold') === 'bold' ? 'selected' : '' }}>Bold</option>
                                                                </select>
                                                            </div>
                                                            @endif
                                                            @if(in_array('text_align', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Align</label>
                                                                <select class="form-select form-select-sm live-input" name="layout[{{ $key }}][text_align]" data-element="{{ $key }}" data-prop="text_align">
                                                                    <option value="left" {{ ($layout[$key]['text_align'] ?? 'right') === 'left' ? 'selected' : '' }}>Left</option>
                                                                    <option value="center" {{ ($layout[$key]['text_align'] ?? 'right') === 'center' ? 'selected' : '' }}>Center</option>
                                                                    <option value="right" {{ ($layout[$key]['text_align'] ?? 'right') === 'right' ? 'selected' : '' }}>Right</option>
                                                                </select>
                                                            </div>
                                                            @endif
                                                            @if(in_array('type', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Tipe Kode</label>
                                                                <select class="form-select form-select-sm live-input" name="layout[{{ $key }}][type]" data-element="{{ $key }}" data-prop="type">
                                                                    <option value="barcode" {{ ($layout[$key]['type'] ?? 'barcode') === 'barcode' ? 'selected' : '' }}>Barcode</option>
                                                                    <option value="qrcode" {{ ($layout[$key]['type'] ?? 'barcode') === 'qrcode' ? 'selected' : '' }}>QR Code</option>
                                                                </select>
                                                            </div>
                                                            @endif
                                                            @if(in_array('border_radius', $el['fields']))
                                                            <div class="col-6">
                                                                <label class="form-label form-label-sm">Border Radius (mm)</label>
                                                                <input type="number" step="0.5" min="0" class="form-control form-control-sm live-input" name="layout[{{ $key }}][border_radius]"
                                                                    value="{{ $layout[$key]['border_radius'] ?? 2 }}" data-element="{{ $key }}" data-prop="border_radius" />
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach

                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-save me-2"></i>Simpan Desain
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                {{-- ===================== TAB 2: CETAK KARTU ===================== --}}
                <div class="tab-pane fade" id="tab_cetak" role="tabpanel">
                    <form action="{{ route('student-card-setting.print') }}" method="POST" target="_blank" id="printForm">
                        @csrf
                        <div class="row g-5">
                            {{-- Filter --}}
                            <div class="col-lg-4">
                                <div class="card card-flush border-0 shadow-[0_8px_30px_rgba(0,0,0,0.04)]" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); border: none;">
                                    <div class="card-header border-0 pb-0">
                                        <text-h2 class="text-h2 card-title fw-bolder mb-0">Filter Santri</text-h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">UPT / Sekolah</label>
                                            <select class="form-select" id="filterSchool">
                                                <option value="">Semua UPT</option>
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Kelas</label>
                                            <select class="form-select" id="filterClassroom">
                                                <option value="">Semua Kelas</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light-primary w-100" id="btnFilter">
                                            <i class="fa-solid fa-sync me-2"></i>Segarkan Data
                                        </button>
                                    </div>
                                    <div class="card-footer">
                                        <label class="form-label fw-bold">Format Cetak</label>
                                        <select class="form-select" name="print_layout">
                                            <option value="pvc">PVC Card (1 kartu/halaman)</option>
                                            <option value="a4_1x1">A4 Grid 1×1 (1 kartu/halaman)</option>
                                            <option value="a4_2x2">A4 Grid 2×2 (4 kartu/halaman)</option>
                                            <option value="a4_2x3">A4 Grid 2×3 (6 kartu/halaman)</option>
                                            <option value="a4_2x4" selected>A4 Grid 2×4 (8 kartu/halaman)</option>
                                            <option value="a4_2x5">A4 Grid 2×5 (10 kartu/halaman)</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary w-100 mt-4" id="btnPrint">
                                            <i class="fa-solid fa-print me-2"></i>Cetak Kartu (<span id="selectedCount">0</span> dipilih)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Student List --}}
                            <div class="col-lg-8">
                                <div class="card card-flush border-0 shadow-[0_8px_30px_rgba(0,0,0,0.04)]" style="border-radius: 24px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); border: none;">
                                    <div class="card-header border-0 pb-0 flex-wrap gap-2">
                                        <text-h2 class="text-h2 card-title fw-bolder mb-0">Daftar Santri</text-h2>
                                        <div class="card-toolbar gap-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted fs-7 fw-bold">Baris:</span>
                                                <select id="rowLimit" class="form-select form-select-sm form-select-solid w-75px" style="background-color: #f5f8fa; border: none; border-radius: 8px;">
                                                    <option value="10" selected>10</option>
                                                    <option value="20">20</option>
                                                    <option value="40">40</option>
                                                </select>
                                            </div>
                                            <div class="position-relative my-1">
                                                <i class="fa-solid fa-magnifying-glass position-absolute top-50 translate-middle-y ms-4 text-slate-400"></i>
                                                <input type="text" id="searchStudent" class="form-control form-control-sm form-control-solid ps-10 w-150px w-md-200px" placeholder="Cari santri..." style="background-color: #f5f8fa; border: none; border-radius: 8px;" />
                                            </div>
                                            <label class="form-check form-check-sm form-check-custom">
                                                <input class="form-check-input" type="checkbox" id="checkAll" />
                                                <span class="form-check-label fw-bold">Pilih Semua</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-row-bordered table-hover align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr class="fw-bolder text-muted">
                                                        <th class="w-25px ps-4"></th>
                                                        <th>Nama</th>
                                                        <th>NIS</th>
                                                        <th>Kelas</th>
                                                        <th>Sekolah</th>
                                                        <th>Riwayat Cetak</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentTableBody">
                                                    <tr><td colspan="6" class="text-center text-muted py-10">Memuat data...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between py-4" id="paginationWrapper" style="border-top: 1px solid #eff2f5;">
                                        <text-caption id="paginationInfo" class="text-caption"></text-caption>
                                        <div id="paginationControls" class="d-flex gap-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
            <!--end::Tab Content-->

        </div>
    </div>
    <!--end::Post-->
</div>
<!--end::Content-->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const SCALE = 4; // 1mm = 4px in preview

    // Element ID mapping
    const elemMap = {
        logo: document.getElementById('prevLogo'),
        title: document.getElementById('prevTitle'),
        subtitle: document.getElementById('prevSubtitle'),
        photo: document.getElementById('prevPhoto'),
        name: document.getElementById('prevName'),
        nis: document.getElementById('prevNis'),
        classroom: document.getElementById('prevClassroom'),
        school: document.getElementById('prevSchool'),
        code: document.getElementById('prevCode'),
    };

    // ── Live preview update ──
    document.querySelectorAll('.live-input').forEach(function(input) {
        input.addEventListener('input', function() {
            const el = this.dataset.element;
            const prop = this.dataset.prop;
            const val = this.value;
            const target = elemMap[el];
            if (!target) return;

            switch(prop) {
                case 'top':
                    target.style.top = (parseFloat(val) * SCALE) + 'px';
                    break;
                case 'left':
                    target.style.left = (parseFloat(val) * SCALE) + 'px';
                    if (el === 'title' || el === 'subtitle') {
                        target.style.width = ((86 - parseFloat(val)) * SCALE) + 'px';
                    }
                    break;
                case 'width':
                    target.style.width = (parseFloat(val) * SCALE) + 'px';
                    break;
                case 'height':
                    target.style.height = (parseFloat(val) * SCALE) + 'px';
                    break;
                case 'color':
                    target.style.color = val;
                    break;
                case 'font_size':
                    target.style.fontSize = (parseInt(val) * 1.2) + 'px';
                    break;
                case 'font_weight':
                    target.style.fontWeight = val;
                    break;
                case 'text_align':
                    target.style.textAlign = val;
                    break;
                case 'text':
                    target.textContent = val;
                    break;
                case 'border_radius':
                    target.style.borderRadius = (parseFloat(val) * SCALE) + 'px';
                    break;
                case 'type':
                    var label = document.getElementById('prevCodeLabel');
                    if (label) label.textContent = val === 'qrcode' ? '▣ QR' : '||||| BARCODE |||||';
                    break;
            }
        });
    });

    // ── Show/Hide toggles ──
    document.querySelectorAll('.elem-show-toggle').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var target = elemMap[this.dataset.element];
            if (target) target.style.display = this.checked ? '' : 'none';
        });
    });

    // ── Background image preview ──
    document.getElementById('bgUpload').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('prevBg').style.backgroundImage = 'url(' + ev.target.result + ')';
            };
            reader.readAsDataURL(file);
        }
    });

    // ── Tab 2: Classroom loader ──
    function loadClassrooms(schoolId) {
        var classSelect = document.getElementById('filterClassroom');
        classSelect.innerHTML = '<option value="">Semua Kelas</option>';
        if (schoolId) {
            return fetch('{{ url("student/school") }}/' + schoolId)
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP status ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    data.forEach(function(c) {
                        classSelect.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
                    });
                })
                .catch(function(err) {
                    console.error('Gagal memuat data kelas:', err);
                });
        }
        return Promise.resolve();
    }

    // ── Tab 2: Search students (Database Driven, Reactive & Paged) ──
    let currentPage = 1;

    function fetchStudents(page = 1) {
        currentPage = page;
        var params = new URLSearchParams();
        var schoolId = document.getElementById('filterSchool').value;
        var classroomId = document.getElementById('filterClassroom').value;
        var query = document.getElementById('searchStudent').value;
        var limit = document.getElementById('rowLimit').value;

        if (schoolId) params.append('school_id', schoolId);
        if (classroomId) params.append('classroom_id', classroomId);
        if (query) params.append('q', query);
        params.append('limit', limit);
        params.append('page', page);

        var tbody = document.getElementById('studentTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data...</td></tr>';

        fetch('{{ route("student-card-setting.get-students") }}?' + params.toString())
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP status ' + r.status);
                return r.json();
            })
            .then(function(response) {
                var students = response.data || [];
                if (students.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-10">Tidak ada data santri ditemukan</td></tr>';
                    renderPagination(response);
                    return;
                }
                var html = '';
                students.forEach(function(s) {
                    var printHistoryHtml = '';
                    if (s.print_count > 0) {
                        printHistoryHtml = '<span class="badge bg-light-success text-emerald-600 fw-bold px-3 py-1 rounded" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981;">' + s.print_count + 'x Dicetak</span>';
                        if (s.last_printed_at) {
                            var byText = s.last_printed_by ? ' oleh ' + s.last_printed_by : '';
                            printHistoryHtml += '<div class="text-slate-400 fst-italic mt-1" style="font-size: 11px;">Terakhir: ' + s.last_printed_at + byText + '</div>';
                        }
                    } else {
                        printHistoryHtml = '<span class="badge bg-light text-muted px-3 py-1 rounded" style="background-color: rgba(243, 244, 246, 1); color: #9ca3af;">Belum Pernah</span>';
                    }

                    html += '<tr>';
                    html += '<td class="ps-4"><input type="checkbox" class="form-check-input student-check" name="student_ids[]" value="' + s.id + '" /></td>';
                    html += '<td class="fw-bold text-slate-800">' + s.name + '</td>';
                    html += '<td>' + (s.nis || '-') + '</td>';
                    html += '<td>' + s.classroom + '</td>';
                    html += '<td>' + s.school + '</td>';
                    html += '<td>' + printHistoryHtml + '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
                updateSelectedCount();
                renderPagination(response);
            })
            .catch(function(err) {
                console.error('Gagal memuat data santri:', err);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-10">Gagal memuat data. Periksa koneksi atau coba refresh halaman.</td></tr>';
            });
    }

    function renderPagination(pagination) {
        var info = document.getElementById('paginationInfo');
        var controls = document.getElementById('paginationControls');

        if (!pagination.total || pagination.total === 0) {
            info.innerHTML = 'Menampilkan 0 sampai 0 dari 0 santri';
            controls.innerHTML = '';
            return;
        }

        // Information text
        info.innerHTML = 'Menampilkan ' + pagination.from + ' sampai ' + pagination.to + ' dari ' + pagination.total + ' santri';

        var html = '';
        // Previous page button
        if (pagination.current_page > 1) {
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + (pagination.current_page - 1) + '"><i class="fa-solid fa-angle-left"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-sm btn-light px-3 py-1 rounded text-muted" disabled><i class="fa-solid fa-angle-left"></i></button>';
        }

        // Pages numbers
        var startPage = Math.max(1, pagination.current_page - 2);
        var endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        if (startPage > 1) {
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="1">1</button>';
            if (startPage > 2) {
                html += '<span class="text-muted align-self-center px-1">...</span>';
            }
        }

        for (var p = startPage; p <= endPage; p++) {
            if (p === pagination.current_page) {
                html += '<button type="button" class="btn btn-sm btn-primary px-3 py-1 rounded fw-bold" style="background-color: #2563eb;">' + p + '</button>';
            } else {
                html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + p + '">' + p + '</button>';
            }
        }

        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                html += '<span class="text-muted align-self-center px-1">...</span>';
            }
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + pagination.last_page + '">' + pagination.last_page + '</button>';
        }

        // Next page button
        if (pagination.current_page < pagination.last_page) {
            html += '<button type="button" class="btn btn-sm btn-light-primary px-3 py-1 rounded" data-page="' + (pagination.current_page + 1) + '"><i class="fa-solid fa-angle-right"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-sm btn-light px-3 py-1 rounded text-muted" disabled><i class="fa-solid fa-angle-right"></i></button>';
        }

        controls.innerHTML = html;

        // Wire pagination click events
        controls.querySelectorAll('[data-page]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var p = parseInt(this.getAttribute('data-page'));
                fetchStudents(p);
            });
        });
    }

    // Debounce for typing search
    var searchTimeout = null;
    document.getElementById('searchStudent').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            fetchStudents(1);
        }, 300);
    });

    // Reactive dropdown filters (Auto load on changes)
    document.getElementById('filterSchool').addEventListener('change', function() {
        loadClassrooms(this.value).then(function() {
            fetchStudents(1);
        });
    });

    document.getElementById('filterClassroom').addEventListener('change', function() {
        fetchStudents(1);
    });

    document.getElementById('rowLimit').addEventListener('change', function() {
        fetchStudents(1);
    });

    document.getElementById('btnFilter').addEventListener('click', function() {
        fetchStudents(1);
    });

    // Initial load: Fetch classrooms (if school is pre-selected) and auto-fetch all students immediately
    var initialSchoolId = document.getElementById('filterSchool').value;
    if (initialSchoolId) {
        loadClassrooms(initialSchoolId).then(function() {
            fetchStudents(1);
        });
    } else {
        fetchStudents(1);
    }

    // ── Check all ──
    document.getElementById('checkAll').addEventListener('change', function() {
        var checked = this.checked;
        document.querySelectorAll('.student-check').forEach(function(cb) { cb.checked = checked; });
        updateSelectedCount();
    });

    // ── Selected count ──
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('student-check')) updateSelectedCount();
    });

    function updateSelectedCount() {
        var count = document.querySelectorAll('.student-check:checked').length;
        document.getElementById('selectedCount').textContent = count;
    }

    // ── Print validation ──
    document.getElementById('printForm').addEventListener('submit', function(e) {
        var count = document.querySelectorAll('.student-check:checked').length;
        if (count === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 santri untuk dicetak');
        }
    });
});
</script>
@endpush
