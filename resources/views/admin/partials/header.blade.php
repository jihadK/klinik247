@php
    $user = $currentUser ?? auth()->user();
    $initial = strtoupper(mb_substr($user->full_name, 0, 1));
    $siteName = $user->isSuperAdmin() ? 'Super Admin' : ($user->site?->name ?? '—');
@endphp

<div id="kt_header" class="header align-items-stretch">
    <div class="container-fluid d-flex align-items-stretch justify-content-between">

        <div class="d-flex align-items-center d-lg-none ms-n4 me-1" title="Show aside menu">
            <div class="btn btn-icon btn-active-color-white" id="kt_aside_mobile_toggle">
                <i class="ki-outline ki-burger-menu fs-1"></i>
            </div>
        </div>

        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="{{ route('admin.dashboard') }}" class="d-lg-none">
                <strong class="fs-3 text-white">{{ config('app.name') }}</strong>
            </a>
        </div>

        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">

            {{-- Site indicator (di sebelah kiri, biar admin tahu mereka di mana) --}}
            <div class="d-flex align-items-center ms-2">
                @if($user->isSuperAdmin())
                    <span class="badge badge-light-danger fs-7 fw-bold">
                        <i class="ki-outline ki-shield-tick fs-7 me-1"></i> SUPER ADMIN
                    </span>
                @else
                    <span class="badge badge-light-info fs-7 fw-bold">
                        <i class="ki-outline ki-shop fs-7 me-1"></i> {{ $siteName }}
                    </span>
                @endif
            </div>

            <div class="topbar d-flex align-items-stretch flex-shrink-0">

                {{-- User menu --}}
                <div class="d-flex align-items-stretch" id="kt_header_user_menu_toggle">
                    <div class="topbar-item cursor-pointer symbol px-3 px-lg-5 me-n3 me-lg-n5 symbol-30px symbol-md-40px"
                         data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                         data-kt-menu-placement="bottom-end" data-kt-menu-flip="bottom">
                        <div class="symbol-label fs-3 bg-light-primary text-primary fw-bold">{{ $initial }}</div>
                    </div>

                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-300px"
                         data-kt-menu="true">
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label fs-2 bg-light-primary text-primary fw-bold">{{ $initial }}</div>
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        {{ $user->full_name }}
                                        <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">
                                            {{ ucfirst($user->role->name ?? '—') }}
                                        </span>
                                    </div>
                                    <span class="fw-semibold text-muted fs-7">{{ $user->email ?? $user->username }}</span>
                                    <span class="fw-semibold text-muted fs-8 mt-1">
                                        @if($user->isSuperAdmin())
                                            <i class="ki-outline ki-shield-tick"></i> Akses lintas klinik
                                        @else
                                            <i class="ki-outline ki-shop"></i> {{ $siteName }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <a href="#" class="menu-link px-5">
                                <span class="menu-text">My Profile</span>
                            </a>
                        </div>
                        <div class="menu-item px-5">
                            <a href="#" class="menu-link px-5">
                                <span class="menu-text">Ganti Password</span>
                            </a>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <form method="POST" action="{{ route('admin.logout') }}" id="kt_logout_form">
                                @csrf
                                <button type="submit" class="menu-link px-5 w-100 text-start bg-transparent border-0">
                                    <span class="menu-icon"><i class="ki-outline ki-exit-right fs-2"></i></span>
                                    <span class="menu-text">Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
