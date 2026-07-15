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
<livewire:styles />
{{-- <style>
    /* .table td:first-child,
      .table th:first-child,
      .table tr:first-child {
          padding-left: 10px;
      } */

    /* table.dataTable td.dt-control::before {
          display: none !important;
      } */

    /* ubah warna primary ke hijau */
    .btn-primary {
        color: #fff;
        background-color: #0BB783 !important;
        border-color: #0BB783 !important;
    }

    /* ubah paginate_button ke hijau */
    /* .paginate_button page-item active */
    /* .paginate_button.page-item.active .page-link {
        color: #fff;
        background-color: #0BB783 !important;
        border-color: #0BB783 !important;
    }

    .header-fixed.toolbar-fixed .wrapper {
        padding-top: 5rem !important;
    }

    .aside.aside-dark .aside-logo {
        background-color: rgba(0, 187, 131, 0.8);
    }

    .aside-menu {
        background: linear-gradient(174.06deg, #0BB783 -14.74%, #B9DDED 95.3%);
    }

    .aside-footer {
        background-color: #0BB783;
        color: #ffffff;
    } */


    .paginate_button.page-item.active .page-link {
        color: #fff;
        background-color: #2FD5C5 !important;
        /* Teal sebagai warna utama untuk tombol */
        border-color: #2FD5C5 !important;
    }

    .header-fixed.toolbar-fixed .wrapper {
        padding-top: 5rem !important;
    }

    .aside.aside-dark .aside-logo {
        background-color: #ffffff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        /* Light background untuk logo */
    }

    .aside-menu {
        background: linear-gradient(174.06deg, #2FD5C5 -14.74%, #C9E5C5 95.3%);
        /* Gradasi teal dan hijau muda untuk menu */
    }

    .aside-footer {
        background-color: #FFF7DC;
        /* Kuning pucat dengan sedikit sentuhan teal */
        color: #263238;
    }

    .aside-dark .menu .menu-item .menu-section,
    .menu-title,
    .btn-label {
        color: #fff !important;
    }

    .aside-dark .menu .menu-item .menu-link,
    .aside-dark .menu .menu-item .menu-link.active {
        color: #9899ac;
    }

    .aside-dark .menu .menu-item .menu-link:hover:not(.disabled):not(.active),
    .aside-dark .menu .menu-item.hover>.menu-link:not(.disabled):not(.active),
    .aside-dark .menu .menu-item .menu-link.active {
        background-color: #0BB783 !important;
    }

    .aside-dark .hover-scroll-overlay-y {
        scrollbar-color: #0BB783 transparent;
    }

    .btn-label {
        font-size: 13px
    }

    .img_profile {
        width: 10rem;
        height: 10rem;
        border-radius: 50%;
        object-fit: cover;
    }

    table.profile td {
        font-size: 13px;
        font-weight: 500;
        padding-bottom: 0.8rem;
    }

    .grey {
        color: #7e8299;
    }

    .nowrap {
        text-wrap: nowrap;
    }

    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
        left: 50%;
    }

    button.detail {
        border: none;
    }



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

    @media screen and (max-width: 395px) {

        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            left: 20%;
            margin-top: 0;
        }
    }
</style> --}}
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

    /* Warna logo (ungu dengan opacity) */
    .aside.aside-dark .aside-logo {
        background-color: rgba(138, 99, 210, 0.8);
    }

    /* Gradasi menu (ungu dan lavender) */
    /* .aside-menu {
        background: linear-gradient(174.06deg, #8a63d2 -14.74%, #d1c6f3 95.3%);
    } */
    .aside-menu {
        background-color: #8a63d2;
    }

    /* Warna footer (ungu dengan gradasi lavender) */
    .aside-footer {
        background-color: #9979d3;
        color: #263238;
    }

    /* Warna teks pada sidebar gelap */
    .aside-dark .menu .menu-item .menu-section,
    .menu-title,
    .btn-label {
        color: #fff !important;
    }

    /* Warna link pada sidebar gelap (abu-abu) */
    .aside-dark .menu .menu-item .menu-link {
        color: #9899ac !important;
    }

    /* Warna background link pada sidebar gelap saat hover (ungu) */
    .aside-dark .menu .menu-item .menu-link:hover:not(.disabled):not(.active),
    .aside-dark .menu .menu-item.hover>.menu-link:not(.disabled):not(.active) {
        background-color: rgba(255, 255, 255, 0.06) !important;
        color: #ffffff !important;
    }

    /* Active menu item styling */
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

    /* Scrollbar pada sidebar gelap (ungu) */
    .aside-dark .hover-scroll-overlay-y {
        scrollbar-color: #8a63d2 transparent;
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

    /* Refine Sidebar Menu Spacing (Gap & Padding) */
    .aside-dark .menu > .menu-item {
        margin-top: 6px !important;
        margin-bottom: 6px !important;
    }

    .aside-dark .menu > .menu-item > .menu-link {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }

    .aside-dark .menu .menu-sub .menu-item {
        margin-top: 4px !important;
        margin-bottom: 4px !important;
    }

    .aside-dark .menu .menu-sub .menu-item .menu-link {
        padding-top: 10px !important;
        padding-bottom: 10px !important;
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

    @keyframes dt-spin {
        to { transform: rotate(360deg); }
    }
</style>

@stack('css')