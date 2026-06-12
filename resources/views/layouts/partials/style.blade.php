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
        background-color: #4C265C !important;
        color: #ffffff !important;
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
</style>

@stack('css')