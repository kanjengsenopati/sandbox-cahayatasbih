<div id="kt_aside" class="aside aside-dark aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
    data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_aside_mobile_toggle">
    <!--begin::Brand-->
    <div class="aside-logo flex-column-auto" id="kt_aside_logo">
        <!--begin::Logo-->
        <div class="d-flex w-100 justify-content-center">
            <a href="#">
                <img alt="Logo" src="{{ asset('assets\media\logos\logo-full.png') }}" class="h-50px logo" />
            </a>
        </div>
        <!--end::Logo-->
        <!--begin::Aside toggler-->
        <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="aside-minimize">
            <!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
            <span class="svg-icon svg-icon-1 rotate-180">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path opacity="0.5"
                        d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                        fill="black" />
                    <path
                        d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                        fill="black" />
                </svg>
            </span>
            <!--end::Svg Icon-->
        </div>
        <!--end::Aside toggler-->
    </div>
    <!--end::Brand-->
    <!--begin::Aside menu-->
    <div class="aside-menu flex-column-fluid">
        <!--begin::Aside Menu-->
        <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true"
            data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside_menu"
            data-kt-scroll-offset="0">
            <!--begin::Menu-->
            <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
                id="#kt_aside_menu" data-kt-menu="true">
                <div class="menu-item">
                    <div class="menu-content pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Menu Utama</span>
                    </div>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('dashboard.*') ? ' active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <span class="menu-icon">
                            <i class="fa-solid fa-house" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                @canany(['Manage Role', 'Manage Admin', 'Manage Informasi', 'Manage Metode Pembayaran','Manage Menu
                Aplikasi', 'Manage Kontak Bantuan', 'Manage Bank', 'Manage Pengaturan Aplikasi', 'Item Bayar',
                'Manage Jenis Bayar'])
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs(['permission.*', 'role.*', 'information-category.*',
                    'information.*', 'payment-method.*', 'application-setting.*', 'application-menu.*', 'help.*',
                    'app-information.*', 'bill-item.*', 'bill-type.*', 'admin.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-cog" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu Pengaturan</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        @can('permission')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('permission.*') ? ' active' : '' }}"
                                href="{{ route('permission.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Permission</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Role')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('role.*') ? ' active' : '' }}"
                                href="{{ route('role.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Role</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Admin')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('admin.*') ? ' active' : '' }}"
                                href="{{ route('admin.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Pengguna</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Informasi')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('information.*', 'information-category.*') ? ' active' : '' }}"
                                href="{{ route('information.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Informasi</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Metode Pembayaran')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('payment-method.*') ? ' active' : '' }}"
                                href="{{ route('payment-method.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Metode Pembayaran</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Pengaturan Aplikasi')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('application-setting.*') ? ' active' : '' }}"
                                href="{{ route('application-setting.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Pengaturan Aplikasi</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Menu Aplikasi')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('application-menu.*') ? ' active' : '' }}"
                                href="{{ route('application-menu.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Menu Aplikasi</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Kontak Bantuan')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('help.*') ? ' active' : '' }}"
                                href="{{ route('help.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Kontak Bantuan</span>
                            </a>
                        </div>
                        @endcan
                        @can('app-information')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('app-information.*') ? ' active' : '' }}"
                                href="{{ route('app-information.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Informasi Aplikasi</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Item Bayar')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('bill-item.*') ? ' active' : '' }}"
                                href="{{ route('bill-item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Item Bayar</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Jenis Bayar')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('bill-type.*') ? ' active' : '' }}"
                                href="{{ route('bill-type.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Jenis Bayar</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Bank')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('bank.*') ? ' active' : '' }}"
                                href="{{ route('bank.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Bank</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
                @endcanany

                @canany(['Manage Wali Santri', 'Manage Santri', 'Manage Sekolah','Manage PPDB', 'Manage Tahun Ajaran',
                'Manage Kenaikan Kelas'])
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['school.*', 'academic-year.*', 'semester.*', 'study.*',
                    'user.*', 'student.*',
                      'category-item.*', 'item.*', 'stock-history.*', 'grade-promotion.*', 'ppdb.*', 'ppdb-registration.*']) ? 'show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="fa-solid fa-school" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Master Data</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        @can('Manage Sekolah')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('school.*') ? ' active' : '' }}"
                                href="{{ route('school.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Sekolah</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Tahun Ajaran')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('academic-year.*') ? ' active' : '' }}"
                                href="{{ route('academic-year.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Tahun Ajaran</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Semester')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('semester.*') ? ' active' : '' }}"
                                href="{{ route('semester.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Semester</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Mata Pelajaran')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('study.*') ? ' active' : '' }}"
                                href="{{ route('study.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Mata Pelajaran</span>
                            </a>
                        </div>
                        @endcan

                        @can('Manage Wali Santri')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('user.*') ? ' active' : '' }}"
                                href="{{ route('user.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Wali Siswa</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Santri')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('student.*') ? ' active' : '' }}"
                                href="{{ route('student.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Siswa</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Kenaikan Kelas')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('grade-promotion.*') ? ' active' : '' }}"
                                href="{{ route('grade-promotion.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Kenaikan Kelas</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Barang')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('category-item.*') ? ' active' : '' }}"
                                href="{{ route('category-item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Kategori Barang</span>
                            </a>
                        </div>
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('item.*') ? ' active' : '' }}"
                                href="{{ route('item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Barang</span>
                            </a>
                        </div>
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('stock-history.*') ? ' active' : '' }}"
                                href="{{ route('stock-history.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Inventori Barang</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage PPDB')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('ppdb.*','ppdb-registration.*') ? ' active' : '' }}"
                                href="{{ route('ppdb.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data PPDB</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
                @endcanany

                @canany(['Manage Saldo Santri', 'Manage Tabungan Santri','Manage Jadwal', 'Manage Tahfidz', 'Manage Pos
                Kasir', 'Manage Tagihan', 'Manage Perilaku Santri'])
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['bill.*', 'saldo-history.*', 'saving-history.*', 'order-item.*', 'study-grade.*',
                    'tahfidz.*', 'student-counseling-score.*', 'schedule.*','saldo-bank.*', 'saving-bank.*']) ? 'show' : '' }}">
                    <span class=" menu-link ">
                        <span class=" menu-icon">
                            <i class="fa-solid fa-edit" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Entri Data</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        @can('Manage Tagihan')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('bill.*') ? ' active' : '' }}"
                                href="{{ route('bill.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Tagihan</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Saldo Santri')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('saldo-history.*', 'saldo-bank.*'
                            ) ? ' active' : '' }}" href="{{ route('saldo-history.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Saldo Santri</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Tabungan Santri')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('saving-history.*', 'saving-bank.*'
                            ) ? ' active' : '' }}" href="{{ route('saving-history.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Tabungan Santri</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Pos Kasir')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('order-item.*') ? ' active' : '' }}"
                                href="{{ route('order-item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">POS Kasir</span>
                            </a>
                        </div>
                        @endcan

                        @can('Manage Nilai Pelajaran')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('study-grade.*') ? ' active' : '' }}"
                                href="{{ route('study-grade.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Nilai Pelajaran</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Tahfidz')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('tahfidz.*') ? ' active' : '' }}"
                                href="{{ route('tahfidz.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Tahfidz</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Perilaku Santri')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('student-counseling-score.*') ? ' active' : '' }}"
                                href="{{ route('student-counseling-score.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Perilaku Santri</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Jadwal')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('schedule.*') ? ' active' : '' }}"
                                href="{{ route('schedule.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Jadwal Agenda</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
                @endcanany

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs(['report-bill.*', 'report-student.*','report-tahfidz.*',
                    'report-student-counseling-score.*', 'report-app-fee.*',
                    'report-saldo.*', 'report-study-grade.*', 'order-item-history.*'
                    ]) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-file" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Laporan</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        @can('Manage Laporan Pos Kasir')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('order-item-history.*') ? ' active' : '' }}"
                                href="{{ route('order-item-history.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">POS Kasir</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Laporan Tagihan')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('report-bill.*') ? ' active' : '' }}"
                                href="{{ route('report-bill.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Tagihan</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Laporan Santri')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('report-student.*') ? ' active' : '' }}"
                                href="{{ route('report-student.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Siswa</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Laporan Nilai Akademik')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('report-study-grade.*') ? ' active' : '' }}"
                                href="{{ route('report-study-grade.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Nilai Akademik</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Laporan Tahfidz')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('report-tahfidz.*') ? ' active' : '' }}"
                                href="{{ route('report-tahfidz.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Tahfidz</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Laporan Perilaku Siswa')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('report-student-counseling-score.*') ? ' active' : '' }}"
                                href="{{ route('report-student-counseling-score.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Perilaku Siswa</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Laporan Saldo Santri')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('report-saldo.*') ? ' active' : '' }}"
                                href="{{ route('report-saldo.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Saldo Santri</span>
                            </a>
                        </div>
                        @endcan
                        @can('Manage Laporan Fee Aplikasi')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('report-app-fee.*') ? ' active' : '' }}"
                                href="{{ route('report-app-fee.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Fee Aplikasi</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>

            </div>
            <!--end::Menu-->
        </div>
        <!--end::Aside Menu-->
    </div>
    <!--end::Aside menu-->
    <!--begin::Footer-->
    <div class="aside-footer flex-column-auto pt-5 pb-7 px-5" id="kt_aside_footer">
        <!--begin::Menu-->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-custom btn-primary w-100">
                <span class="btn-label">Logout</span>
                <!--begin::Svg Icon | path: icons/duotune/general/gen005.svg-->
                <span class="svg-icon btn-icon svg-icon-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path opacity="0.3"
                            d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM15 17C15 16.4 14.6 16 14 16H8C7.4 16 7 16.4 7 17C7 17.6 7.4 18 8 18H14C14.6 18 15 17.6 15 17ZM17 12C17 11.4 16.6 11 16 11H8C7.4 11 7 11.4 7 12C7 12.6 7.4 13 8 13H16C16.6 13 17 12.6 17 12ZM17 7C17 6.4 16.6 6 16 6H8C7.4 6 7 6.4 7 7C7 7.6 7.4 8 8 8H16C16.6 8 17 7.6 17 7Z"
                            fill="black" />
                        <path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="black" />
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </button>
        </form>
    </div>
    <!--end::Footer-->
</div>