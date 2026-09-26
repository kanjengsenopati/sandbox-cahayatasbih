<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
{{-- <title>{{ env('APP_NAME') }} {{ @$title ? '| ' . $title : '' }}</title> --}}
<title>{{ @$title ? $title . ' | ' : '' }}{{ env('APP_NAME') }} </title>
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

{{-- start favicon --}}
<link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/media/logos/favicon/apple-icon-57x57.png') }}">
<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/media/logos/favicon/apple-icon-60x60.png') }}">
<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/media/logos/favicon/apple-icon-72x72.png') }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/media/logos/favicon/apple-icon-76x76.png') }}">
<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/media/logos/favicon/apple-icon-114x114.png') }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/media/logos/favicon/apple-icon-120x120.png') }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/media/logos/favicon/apple-icon-144x144.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/media/logos/favicon/apple-icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/media/logos/favicon/apple-icon-180x180.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/media/logos/favicon/android-icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/logos/favicon/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/media/logos/favicon/favicon-96x96.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/media/logos/favicon/favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('assets/media/logos/favicon/manifest.json') }}">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="{{ asset('assets/media/logos/favicon/ms-icon-144x144.png') }}">
<meta name="theme-color" content="#ffffff">
{{-- end favicon --}}

<link rel="canonical" href="{{ env('APP_NAME') }}" />
<!--begin::Fonts-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<!--end::Fonts-->
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<!--end::Page Vendor Stylesheets-->
<meta name="csrf-token" content="{{ csrf_token() }}" />
<!--end::Fonts-->
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
    type="text/css" />
<!--end::Page Vendor Stylesheets-->
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />

<link href="{{ url('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css') }}" rel="stylesheet" />

<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
<!--end::Page Vendor Stylesheets-->

{{--
<link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@livewireStyles
<style>
    /* Font Inter */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    /* Warna primary (ungu) */
    .btn-primary {
        color: #fff;
        background-color: #8a63d2 !important;
        border-color: #8a63d2 !important;
    }

    /* Warna tombol pagination yang aktif (ungu) */
    .paginate_button.page-item.active .page-link {
        color: #fff;
        background-color: #8a63d2 !important;
        border-color: #8a63d2 !important;
    }

    /* Warna logo (light) */
    .aside.aside-dark .aside-logo {
        background-color: #ffffff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    /* Deep purple/burgundy sidebar background color */
    .aside-menu {
        background-color: #5A306B !important;
    }

    /* Deep purple footer background color */
    .aside-footer {
        background-color: #4C265C !important;
        color: #ffffff !important;
    }

    /* Warna teks pada sidebar gelap */
    .aside-dark .menu .menu-item .menu-section,
    .menu-title,
    .btn-label {
        color: #fff !important;
    }

    /* Warna link pada sidebar gelap */
    .aside-dark .menu .menu-item .menu-link {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    /* Warna background link pada sidebar gelap saat hover */
    .aside-dark .menu .menu-item .menu-link:hover:not(.disabled):not(.active),
    .aside-dark .menu .menu-item.hover>.menu-link:not(.disabled):not(.active) {
        background-color: rgba(255, 255, 255, 0.04) !important;
        color: #ffffff !important;
    }

    /* Active menu item styling: full width, flat, border-radius: 0 */
    .aside-dark .menu .menu-item .menu-link.active {
        background-color: rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    /* Sub-menu active link styling (with left border alignment) */
    .aside-dark .menu .menu-sub .menu-item .menu-link.active {
        border-left: 4px solid #10B981 !important; /* Emerald success left accent border */
        padding-left: 32px !important; /* 36px normal - 4px border = 32px to keep text aligned */
    }

    /* Top-level active link styling (with left border alignment) */
    .aside-dark .menu > .menu-item > .menu-link.active {
        border-left: 4px solid #10B981 !important; /* Emerald success left accent border */
        padding-left: 20px !important; /* 24px normal - 4px border = 20px to keep text aligned */
    }

    /* Scrollbar pada sidebar gelap */
    .aside-dark .hover-scroll-overlay-y {
        scrollbar-color: #5A306B transparent;
    }

    /* Font Inter untuk seluruh teks */
    body,
    button,
    input,
    select,
    textarea {
        font-family: 'Inter', sans-serif;
    }

    /* Ukuran font label tombol */
    .btn-label {
        font-size: 13px;
    }

    /* Gaya gambar profil */
    .img_profile {
        width: 10rem;
        height: 10rem;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Gaya tabel profil */
    table.profile td {
        font-size: 13px;
        font-weight: 500;
        padding-bottom: 0.8rem;
    }

    /* Warna teks abu-abu */
    .grey {
        color: #7e8299;
    }

    /* Gaya text-wrap */
    .nowrap {
        text-wrap: nowrap;
    }

    /* Penyesuaian posisi tombol detail pada tabel data */
    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
        left: 20%;
        margin-top: 0;
    }

    /* Penyesuaian margin untuk layout responsif */
    @media (min-width: 991px) {
        .post.d-flex.flex-column-fluid {
            margin-top: 4rem !important;
        }
    }

    @media (max-width: 991px) {
        .toolbar {
            display: none !important;
        }

        #kt_content_container {
            margin-top: 1rem !important;
        }
    }

    /* Refine Sidebar Menu Spacing (Gap & Padding) - Full-width style with separators */
    .aside-dark .menu > .menu-item {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .aside-dark .menu > .menu-item > .menu-link {
        padding-top: 16px !important;
        padding-bottom: 16px !important;
        padding-left: 24px !important;
        padding-right: 24px !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }

    .aside-dark .menu .menu-sub {
        background-color: #4C265C !important; /* Darker sub-menu background matching active link */
    }

    .aside-dark .menu .menu-sub .menu-item {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    }

    .aside-dark .menu .menu-sub .menu-item:last-child {
        border-bottom: none !important;
    }

    .aside-dark .menu .menu-sub .menu-item .menu-link {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
        padding-left: 36px !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }

    /* Arrow icon for folders and accordion submenus */
    .aside-dark .menu .menu-item .menu-link .menu-arrow:after {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='9 18 15 12 9 6'%3E%3C/polyline%3E%3C/svg%3E") !important;
        width: 12px;
        height: 12px;
    }

    /* --- Custom DataTables Loading/Processing Redesign --- */
    div.dataTables_wrapper {
        position: relative;
        min-height: 200px; /* Prevents layout collapse and gives space for the loading card */
    }

    /* Glassmorphism backdrop overlay on the wrapper */
    div.dataTables_wrapper.dt-processing-active::before {
        content: "" !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: rgba(248, 250, 252, 0.45) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
        z-index: 1040 !important;
        border-radius: 24px !important;
        opacity: 0;
        animation: fadeInOverlay 0.2s ease-in-out forwards;
    }

    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Elevated White/Purple Card for the loading box */
    div.dataTables_wrapper div.dataTables_processing {
        display: none;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 540px !important;
        max-width: 90% !important;
        height: auto !important;
        min-height: 160px !important;
        margin: 0 !important;
        padding: 24px 32px !important;
        background: linear-gradient(135deg, #FAF5FF 0%, #F3E8FF 100%) !important; /* Smooth Purple */
        border: 1px solid rgba(138, 99, 210, 0.2) !important; /* Smooth Purple Border */
        border-radius: 24px !important; /* Mutlak 24px radius */
        box-shadow: 0 10px 40px rgba(138, 99, 210, 0.08) !important;
        z-index: 1050 !important;

        /* Typography matching branding */
        color: #5A306B !important; /* Deep Purple text */
        font-family: 'Inter', sans-serif !important;
        font-size: 20px !important; /* 2x Larger text size */
        font-weight: 600 !important;
        text-align: center !important;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }

    /* Active state (when wrapper is dt-processing-active) */
    div.dataTables_wrapper.dt-processing-active div.dataTables_processing {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 16px !important;
        opacity: 1 !important;
    }

    /* Modern Animated Spinner (Branding Primary: Purple #8a63d2) */
    div.dataTables_wrapper div.dataTables_processing::before {
        content: "" !important;
        display: block !important;
        width: 42px !important;
        height: 42px !important;
        border: 3.5px solid rgba(138, 99, 210, 0.15) !important;
        border-top-color: #8a63d2 !important;
        border-radius: 50% !important;
        animation: dt-spin 0.8s linear infinite !important;
        margin: 0 auto !important;
        position: relative !important;
        z-index: 1052 !important;
    }

    /* ==========================================================================
       GLOBAL TYPOGRAPHY & TABLE READABILITY ENHANCEMENTS (DARKER GRAY CONTRAST)
       ========================================================================== */
    /* Table Headers: Darker gray (#334155 Slate-700 / #1e293b Slate-800), bold, high contrast */
    table thead tr th,
    table thead th,
    table.dataTable thead th,
    table.dataTable thead td,
    .table thead th,
    .table-row-dashed thead tr th,
    .table-row-dashed thead th,
    .text-gray-400.fw-bolder,
    .text-gray-400.fw-bold {
        color: #334155 !important; /* Slate-700 - Darker Gray for Headers */
        font-weight: 700 !important;
        letter-spacing: 0.01em !important;
    }

    /* Table Body Data: Crisp dark gray (#1e293b Slate-800) for maximum readability */
    table tbody tr td,
    table tbody td,
    table.dataTable tbody td,
    .table tbody td,
    .table-row-dashed tbody tr td,
    .table-row-dashed tbody td {
        color: #1e293b !important; /* Slate-800 - Crisp Dark Text for Body Data */
        font-weight: 600 !important;
    }

    /* Global Text Gray Override for Better Contrast */
    .text-gray-400 {
        color: #475569 !important; /* Upgrade from faint #a1a5b7 to Slate-600 */
    }
    .text-gray-500 {
        color: #334155 !important; /* Upgrade from #7e8299 to Slate-700 */
    }
    .text-gray-600 {
        color: #1e293b !important; /* Upgrade from #5e6278 to Slate-800 */
    }

    /* DataTables Controls, Search, and Length Labels */
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        color: #334155 !important; /* Slate-700 */
        font-weight: 600 !important;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
    }

    /* Typography Components Override */
    .typography-label {
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.02em !important;
        color: #334155 !important; /* Slate-700 - Darker Gray */
        font-family: 'Outfit', 'Inter', sans-serif !important;
    }
    .typography-caption {
        font-size: 12px !important;
        font-weight: 500 !important;
        font-style: italic !important;
        color: #475569 !important; /* Slate-600 */
        font-family: 'Inter', sans-serif !important;
    }
</style>

<style>
    /*
     * GLOBAL FIX: Semua ikon di dalam button/anchor tidak menghalangi pointer events
     * Ini memastikan klik pada ikon selalu bubble ke parent button/anchor,
     * sehingga data-* attributes selalu terbaca dari elemen yang benar.
     */
    .btn i,
    .btn svg,
    .btn span.path1,
    .btn span.path2,
    .btn span.path3,
    .btn span.path4,
    .btn span.path5,
    a.btn-delete i,
    a.btn-delete svg,
    [class*="btn-icon"] i,
    [class*="btn-icon"] svg {
        pointer-events: none !important;
    }

    /*
     * Pastikan link WhatsApp dan link external selalu bisa diklik
     * bahkan ketika dibungkus dalam DataTables atau container lain.
     */
    a[href^="https://wa.me"],
    a[href^="https://wa.me"] *,
    td a[target="_blank"],
    td a[target="_blank"] * {
        pointer-events: auto !important;
        cursor: pointer !important;
    }
</style>

@stack('css')