@extends('admin.layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
    {{-- Form side --}}
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <div class="w-lg-500px p-10">
                <form method="POST" action="{{ route('admin.login.attempt') }}" class="form w-100" id="kt_sign_in_form">
                    @csrf

                    <div class="text-center mb-11">
                        <img src="{{ asset('assets/logo/logo-klinik-247-h.png') }}" alt="Klinik247"
                             style="max-height:72px; width:auto;" class="mb-4">
                        <h1 class="text-gray-900 fw-bolder mb-2 fs-2">Portal Administrasi</h1>
                        <div class="text-gray-500 fw-semibold fs-6">Sistem Manajemen Klinik</div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-center mb-8">
                            <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                            <div class="d-flex flex-column">
                                @foreach ($errors->all() as $error)
                                    <span>{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="fv-row mb-8">
                        <label class="form-label fs-6 fw-bolder text-gray-700">Username / Email</label>
                        <input type="text" name="login" placeholder="username atau email"
                               value="{{ old('login') }}" autocomplete="username" autofocus
                               class="form-control bg-transparent" />
                    </div>

                    <div class="fv-row mb-3">
                        <label class="form-label fs-6 fw-bolder text-gray-700">Password</label>
                        <input type="password" name="password" placeholder="Password"
                               autocomplete="current-password"
                               class="form-control bg-transparent" />
                    </div>

                    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="remember" value="1" />
                            <span class="form-check-label text-gray-700">Ingat saya</span>
                        </label>
                        <a href="#" class="link-primary">Lupa password?</a>
                    </div>

                    <div class="d-grid mb-10">
                        <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                            <span class="indicator-label">Sign In</span>
                            <span class="indicator-progress">Mohon tunggu...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>

                    <div class="text-center text-muted fs-7">
                        © {{ date('Y') }} Klinik247 — Multi-tenant Clinic System
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Aside (banner) — Medical Clinic Theme --}}
    <div class="d-flex flex-lg-row-fluid w-lg-50 order-1 order-lg-2 position-relative overflow-hidden"
         style="background: linear-gradient(135deg, #001b3d 0%, #00478d 45%, #005eb8 75%, #006970 100%);">

        {{-- SVG Background Pattern --}}
        <svg class="position-absolute" style="top:0; left:0; width:100%; height:100%; opacity:0.18; pointer-events:none;"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid slice">
            <defs>
                <pattern id="grid" width="48" height="48" patternUnits="userSpaceOnUse">
                    <path d="M48 0 L0 0 0 48" fill="none" stroke="#fff" stroke-width="0.5"/>
                </pattern>
                <radialGradient id="glow1" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#7af1fc" stop-opacity="0.6"/>
                    <stop offset="100%" stop-color="#7af1fc" stop-opacity="0"/>
                </radialGradient>
            </defs>
            <rect width="800" height="1000" fill="url(#grid)"/>
            <circle cx="650" cy="200" r="220" fill="url(#glow1)"/>
            <circle cx="120" cy="800" r="180" fill="url(#glow1)" opacity="0.5"/>
        </svg>

        {{-- Floating decorative medical icons --}}
        <svg class="position-absolute" style="top:0; left:0; width:100%; height:100%; pointer-events:none; opacity:0.55;"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid slice">

            {{-- ECG heartbeat line --}}
            <polyline points="50,500 180,500 210,440 240,560 270,420 300,580 330,500 460,500 490,440 520,560 550,500 750,500"
                      stroke="#7af1fc" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>

            {{-- Stethoscope curve abstract --}}
            <path d="M150,150 Q250,80 350,180 Q420,260 360,360 Q300,440 230,400"
                  stroke="#c8daff" stroke-width="3" fill="none" stroke-linecap="round" opacity="0.7"/>
            <circle cx="230" cy="400" r="14" fill="none" stroke="#c8daff" stroke-width="3" opacity="0.7"/>

            {{-- Floating crosses --}}
            <g fill="#fff" opacity="0.6">
                <g transform="translate(680,120)">
                    <rect x="-3" y="-14" width="6" height="28" rx="1.5"/>
                    <rect x="-14" y="-3" width="28" height="6" rx="1.5"/>
                </g>
                <g transform="translate(90,250)" opacity="0.7">
                    <rect x="-4" y="-18" width="8" height="36" rx="2"/>
                    <rect x="-18" y="-4" width="36" height="8" rx="2"/>
                </g>
                <g transform="translate(700,780)" opacity="0.5">
                    <rect x="-3" y="-12" width="6" height="24" rx="1.5"/>
                    <rect x="-12" y="-3" width="24" height="6" rx="1.5"/>
                </g>
            </g>

            {{-- Floating dots --}}
            <g fill="#7af1fc">
                <circle cx="500" cy="100" r="4" opacity="0.7"/>
                <circle cx="580" cy="160" r="3" opacity="0.5"/>
                <circle cx="120" cy="450" r="5" opacity="0.6"/>
                <circle cx="730" cy="500" r="3" opacity="0.7"/>
                <circle cx="200" cy="900" r="4" opacity="0.6"/>
                <circle cx="640" cy="900" r="6" opacity="0.5"/>
            </g>

            {{-- Pulse rings --}}
            <circle cx="650" cy="650" r="40" fill="none" stroke="#7af1fc" stroke-width="2" opacity="0.4"/>
            <circle cx="650" cy="650" r="80" fill="none" stroke="#7af1fc" stroke-width="1.5" opacity="0.25"/>
            <circle cx="650" cy="650" r="120" fill="none" stroke="#7af1fc" stroke-width="1" opacity="0.15"/>
        </svg>

        {{-- Content overlay --}}
        <div class="d-flex flex-column flex-center py-15 px-5 px-md-15 w-100 position-relative" style="z-index:2;">

            {{-- Logo --}}
            <img src="{{ asset('assets/logo/logo-klinik-247-v.png') }}" alt="Klinik247"
                 style="max-height: 220px; width: auto; filter: drop-shadow(0 12px 32px rgba(0,0,0,0.4));"
                 class="mb-8">

            {{-- Tagline --}}
            <h2 class="text-white fw-bolder text-center mb-4" style="font-size:1.75rem; max-width:480px; line-height:1.3;">
                Layanan Kesehatan<br>
                <span style="background: linear-gradient(90deg, #7af1fc, #c8daff); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">
                    Tanpa Batas Waktu
                </span>
            </h2>

            <div class="text-white text-center mb-8 opacity-75" style="max-width: 460px; font-size: 1rem; line-height: 1.6;">
                Sistem manajemen klinik terintegrasi — pendaftaran, rekam medis,
                pelayanan ibu &amp; anak, hingga portal pasien mandiri.
            </div>

            {{-- Feature pills --}}
            <div class="d-flex flex-wrap justify-content-center gap-2" style="max-width: 500px;">
                <span class="d-inline-flex align-items-center px-3 py-2 rounded-pill text-white" style="background:rgba(255,255,255,0.12); backdrop-filter: blur(8px); border:1px solid rgba(255,255,255,0.2); font-size:0.8rem;">
                    <i class="ki-outline ki-heart text-white me-1 fs-7"></i> ANC &amp; INC
                </span>
                <span class="d-inline-flex align-items-center px-3 py-2 rounded-pill text-white" style="background:rgba(255,255,255,0.12); backdrop-filter: blur(8px); border:1px solid rgba(255,255,255,0.2); font-size:0.8rem;">
                    <i class="ki-outline ki-shield-tick text-white me-1 fs-7"></i> KB &amp; Imunisasi
                </span>
                <span class="d-inline-flex align-items-center px-3 py-2 rounded-pill text-white" style="background:rgba(255,255,255,0.12); backdrop-filter: blur(8px); border:1px solid rgba(255,255,255,0.2); font-size:0.8rem;">
                    <i class="ki-outline ki-people text-white me-1 fs-7"></i> Multi-Tenant
                </span>
                <span class="d-inline-flex align-items-center px-3 py-2 rounded-pill text-white" style="background:rgba(255,255,255,0.12); backdrop-filter: blur(8px); border:1px solid rgba(255,255,255,0.2); font-size:0.8rem;">
                    <i class="ki-outline ki-medical-cross text-white me-1 fs-7"></i> Portal Pasien
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
