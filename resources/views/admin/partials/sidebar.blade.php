<div id="kt_aside" class="aside aside-dark aside-hoverable"
     data-kt-drawer="true" data-kt-drawer-name="aside"
     data-kt-drawer-activate="{default: true, lg: false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="{default:'200px', '300px': '250px'}"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_aside_mobile_toggle">

    {{-- Brand --}}
    <div class="aside-logo flex-column-auto" id="kt_aside_logo">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center">
            <span class="fs-2 fw-bolder text-white">Klinik247</span>
        </a>
        <div id="kt_aside_toggle"
             class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle me-n2"
             data-kt-toggle="true" data-kt-toggle-state="active"
             data-kt-toggle-target="body" data-kt-toggle-name="aside-minimize">
            <i class="ki-outline ki-double-left fs-1 rotate-180"></i>
        </div>
    </div>

    {{-- Menu --}}
    <div class="aside-menu flex-column-fluid">
        <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper"
             data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
             data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer"
             data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="0">

            <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
                 id="#kt_aside_menu" data-kt-menu="true">

                {{-- Dashboard --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <span class="menu-icon"><i class="ki-outline ki-element-11 fs-2"></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                {{-- SUPER ADMIN: Manajemen Klinik --}}
                @if(auth()->user()?->isSuperAdmin())
                    <div class="menu-item">
                        <div class="menu-content pt-8 pb-2">
                            <span class="menu-section text-muted text-uppercase fs-8 ls-1">Super Admin</span>
                        </div>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link" href="#">
                            <span class="menu-icon"><i class="ki-outline ki-shop fs-2"></i></span>
                            <span class="menu-title">Klinik (Sites)</span>
                        </a>
                    </div>
                @endif

                {{-- MASTER DATA --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Master Data</span>
                    </div>
                </div>

                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-people fs-2"></i></span>
                        <span class="menu-title">Pasien</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-medical-cross fs-2"></i></span>
                        <span class="menu-title">Dokter</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-graduation fs-2"></i></span>
                        <span class="menu-title">Spesialisasi</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-pulse fs-2"></i></span>
                        <span class="menu-title">Layanan</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-capsule fs-2"></i></span>
                        <span class="menu-title">Obat</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-calendar fs-2"></i></span>
                        <span class="menu-title">Jadwal Praktek</span>
                    </a>
                </div>

                {{-- TRANSAKSI placeholder (akan dibuat saat ada format manual) --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Pelayanan</span>
                    </div>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Akan dibuat saat ada contoh format manual">
                        <span class="menu-icon"><i class="ki-outline ki-time fs-2"></i></span>
                        <span class="menu-title">Antrian Hari Ini</span>
                        <span class="badge badge-light-warning ms-2">Soon</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                        <span class="menu-icon"><i class="ki-outline ki-notepad-edit fs-2"></i></span>
                        <span class="menu-title">Rekam Medis</span>
                        <span class="badge badge-light-warning ms-2">Soon</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                        <span class="menu-icon"><i class="ki-outline ki-receipt-square fs-2"></i></span>
                        <span class="menu-title">Pembayaran</span>
                        <span class="badge badge-light-warning ms-2">Soon</span>
                    </a>
                </div>

                {{-- SISTEM --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Sistem</span>
                    </div>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-security-user fs-2"></i></span>
                        <span class="menu-title">Manajemen User</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-setting-3 fs-2"></i></span>
                        <span class="menu-title">Pengaturan</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#">
                        <span class="menu-icon"><i class="ki-outline ki-file-up fs-2"></i></span>
                        <span class="menu-title">Audit Log</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer aside --}}
    <div class="aside-footer flex-column-auto pt-5 pb-7 px-5" id="kt_aside_footer">
        <div class="text-center text-muted fs-8">
            v0.1.0 — Phase 0
        </div>
    </div>
</div>
