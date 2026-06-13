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

                {{-- ============ Dashboard ============ --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <span class="menu-icon"><i class="ki-outline ki-element-11 fs-2"></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                {{-- ============ REKAM MEDIS (search semua pasien) ============ --}}
                @if(auth()->user()?->hasPermission('patients.view'))
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.rm.*') ? 'active' : '' }}" href="{{ route('admin.rm.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-document fs-2"></i></span>
                            <span class="menu-title">📋 Rekam Medis Pasien</span>
                        </a>
                    </div>
                @endif

                {{-- ============ Master Klinik (super admin + admin own site) ============ --}}
                @if(auth()->user()?->hasPermission('sites.view'))
                    <div class="menu-item">
                        <div class="menu-content pt-8 pb-2">
                            <span class="menu-section text-muted text-uppercase fs-8 ls-1">Pengaturan</span>
                        </div>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.sites.*') ? 'active' : '' }}" href="{{ route('admin.sites.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-shop fs-2"></i></span>
                            <span class="menu-title">Master Klinik</span>
                        </a>
                    </div>
                @endif

                {{-- ============ PENDAFTARAN ============ --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Pendaftaran</span>
                    </div>
                </div>

                @php
                    $pendaftaranActive = request()->routeIs('admin.patients.*') || request()->routeIs('admin.visits.*');
                @endphp
                <div class="menu-item menu-accordion {{ $pendaftaranActive ? 'here show' : '' }}" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-outline ki-people fs-2"></i></span>
                        <span class="menu-title">Pendaftaran</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}" href="{{ route('admin.patients.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Pasien</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.visits.*') ? 'active' : '' }}" href="{{ route('admin.visits.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Kunjungan Pasien</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ============ PELAYANAN ============ --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Pelayanan</span>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->routeIs('admin.anc.*') ? 'here show' : '' }}" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-outline ki-heart-circle fs-2"></i></span>
                        <span class="menu-title">Maternal Care</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.anc.*') ? 'active' : '' }}" href="{{ route('admin.anc.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Ibu Hamil (ANC)</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.inc.*') ? 'active' : '' }}" href="{{ route('admin.inc.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Persalinan (INC)</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.pnc.*') ? 'active' : '' }}" href="{{ route('admin.pnc.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Nifas (PNC)</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.kn.*') ? 'active' : '' }}" href="{{ route('admin.kn.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Neonatus (KN)</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-outline ki-tag fs-2"></i></span>
                        <span class="menu-title">Bayi & Anak</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.child.*') ? 'active' : '' }}" href="{{ route('admin.child.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Imunisasi & Tumbuh Kembang</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->routeIs('admin.kb.*') ? 'here show' : '' }}" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-outline ki-pulse fs-2"></i></span>
                        <span class="menu-title">KB & Reproduksi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.kb.*') ? 'active' : '' }}" href="{{ route('admin.kb.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Akseptor KB</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Phase 1.x">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Reproduksi</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ============ APOTIK & KASIR ============ --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Apotik & Kasir</span>
                    </div>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Phase 1.8">
                        <span class="menu-icon"><i class="ki-outline ki-capsule fs-2"></i></span>
                        <span class="menu-title">Apotik</span>
                        <span class="badge badge-light-warning ms-2">Soon</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Phase 1.8">
                        <span class="menu-icon"><i class="ki-outline ki-receipt-square fs-2"></i></span>
                        <span class="menu-title">Kasir</span>
                        <span class="badge badge-light-warning ms-2">Soon</span>
                    </a>
                </div>

                {{-- ============ MASTER DATA ============ --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Master Data</span>
                    </div>
                </div>

                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-outline ki-book-square fs-2"></i></span>
                        <span class="menu-title">Master Klinis</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Dokter / Bidan</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Spesialisasi</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Layanan & Tarif</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Obat</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Jadwal Praktek</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-outline ki-data fs-2"></i></span>
                        <span class="menu-title">Master Lookup</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Jenis Pembiayaan</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Alat Kontrasepsi</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Jenis Imunisasi</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Pendidikan</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Agama</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Wilayah</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ============ LAPORAN ============ --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Laporan</span>
                    </div>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Phase 1.10">
                        <span class="menu-icon"><i class="ki-outline ki-chart-line fs-2"></i></span>
                        <span class="menu-title">Laporan</span>
                        <span class="badge badge-light-warning ms-2">Soon</span>
                    </a>
                </div>

                {{-- ============ SISTEM ============ --}}
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Sistem</span>
                    </div>
                </div>

                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-outline ki-setting-2 fs-2"></i></span>
                        <span class="menu-title">Sistem</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Phase 1.9">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Manajemen User</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Role & Permission</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Pengaturan</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="#" data-bs-toggle="tooltip" title="Coming soon">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Audit Log</span>
                                <span class="badge badge-light-warning ms-2">Soon</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer aside --}}
    <div class="aside-footer flex-column-auto pt-5 pb-7 px-5" id="kt_aside_footer">
        <div class="text-center text-muted fs-8">
            v0.2.0 — Phase 1.1
        </div>
    </div>
</div>
