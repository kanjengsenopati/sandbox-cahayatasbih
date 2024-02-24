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
                @can('admin', 'permission', 'role')
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['admin.*', 'role.*', 'permission.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-user-shield" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu Admin</span>
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
                        @can('role')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('role.*') ? ' active' : '' }}"
                                href="{{ route('role.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Role</span>
                            </a>
                        </div>
                        @endcan
                        @can('admin')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.*') ? ' active' : '' }}"
                                href="{{ route('admin.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Admin</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
                @endcan

                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['school.*','academic-year.*','tahfidz.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-school" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Master Data</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('school.*') ? ' active' : '' }}"
                                href="{{ route('school.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Sekolah</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('academic-year.*') ? ' active' : '' }}"
                                href="{{ route('academic-year.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Tahun Ajaran</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('tahfidz.*') ? ' active' : '' }}"
                                href="{{ route('tahfidz.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Tahfidz</span>
                            </a>
                        </div>
                    </div>
                </div>

                @can('user')
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['user.*','student.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-user" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu User</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        @can('user')
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
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('student.*') ? ' active' : '' }}"
                                href="{{ route('student.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Siswa</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endcan
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['saldo-history.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-dollar-sign" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu Saldo</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('saldo-history.*') ? ' active' : '' }}"
                                href="{{ route('saldo-history.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Riwayat Saldo</span>
                            </a>
                        </div>
                    </div>
                </div>


                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['student-achievement.*','student-counseling-score.*','schedule.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-award" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu Prestasi Siswa</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('student-achievement.*') ? ' active' : '' }}"
                                href="{{ route('student-achievement.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Prestasi Siswa</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('student-counseling-score.*') ? ' active' : '' }}"
                                href="{{ route('student-counseling-score.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Nilai Konseling</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('schedule.*') ? ' active' : '' }}"
                                href="{{ route('schedule.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Jadwal Agenda</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['category-item.*','item.*', 'stock-history.*','order-item.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-box" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu POS</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('category-item.*') ? ' active' : '' }}"
                                href="{{ route('category-item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Kategori Barang</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('item.*') ? ' active' : '' }}"
                                href="{{ route('item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Barang</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('stock-history.*') ? ' active' : '' }}"
                                href="{{ route('stock-history.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Stok</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('order-item.*') ? ' active' : '' }}"
                                href="{{ route('order-item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Transaksi</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['bill-item.*','bill-type.*','bill.*','payment-rate.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-money-bill" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Administrasi Siswa</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('bill-item.*') ? ' active' : '' }}"
                                href="{{ route('bill-item.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Item Bayar</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('bill-type.*') ? ' active' : '' }}"
                                href="{{ route('bill-type.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Jenis Bayar</span>
                            </a>
                        </div>
                    </div>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('bill.*') ? ' active' : '' }}"
                                href="{{ route('bill.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Tagihan</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['payment-method.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-gear" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu Pengaturan</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        @can('user')
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('payment-method.*') ? ' active' : '' }}"
                                href="{{ route('payment-method.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Metode Pembayaran</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['information-category.*','information.*']) ? 'show' : '' }}">
                    <span class="menu-link ">
                        <span class="menu-icon">
                            <i class="fa-solid fa-info" style="color: #ffffff;"></i>
                        </span>
                        <span class="menu-title">Menu Informasi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion menu-active-bg">
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('information-category.*') ? ' active' : '' }}"
                                href="{{ route('information-category.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Kategori Informasi</span>
                            </a>
                        </div>
                        <div class="menu-item ">
                            <a class="menu-link {{ request()->routeIs('information.*') ? ' active' : '' }}"
                                href="{{ route('information.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Data Informasi</span>
                            </a>
                        </div>
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