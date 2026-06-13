@extends('portal.layout')
@section('title', 'Cek Rekam Medis')

@section('content')

{{-- ===== Hero Banner (Medical Themed) ===== --}}
<section class="relative w-full overflow-hidden" style="min-height: 520px;">
    {{-- Background layers --}}
    <div class="absolute inset-0 bg-gradient-to-br from-[#001b3d] via-primary to-primary-container"></div>
    <div class="absolute inset-0 hero-radial"></div>
    <div class="absolute inset-0 hero-grid opacity-40"></div>

    {{-- Decorative SVG medical pattern --}}
    <div class="absolute inset-0 pointer-events-none">
        <svg class="absolute right-0 top-0 h-full opacity-25" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" preserveAspectRatio="xMidYMid slice">
            {{-- Big circle backdrop --}}
            <circle cx="450" cy="200" r="220" fill="#7af1fc" opacity="0.15"/>
            <circle cx="450" cy="200" r="160" fill="#c8daff" opacity="0.10"/>
            <circle cx="450" cy="200" r="100" fill="#fff" opacity="0.08"/>

            {{-- Stethoscope-ish curve --}}
            <path d="M100,500 Q200,300 350,350 T580,250" stroke="#7af1fc" stroke-width="3" fill="none" opacity="0.4" stroke-dasharray="6 8"/>

            {{-- Floating medical icons --}}
            <g opacity="0.5">
                {{-- Cross 1 --}}
                <g transform="translate(80,80)">
                    <rect x="-4" y="-16" width="8" height="32" rx="2" fill="#fff"/>
                    <rect x="-16" y="-4" width="32" height="8" rx="2" fill="#fff"/>
                </g>
                {{-- Cross 2 --}}
                <g transform="translate(520,420)">
                    <rect x="-3" y="-12" width="6" height="24" rx="1.5" fill="#7af1fc"/>
                    <rect x="-12" y="-3" width="24" height="6" rx="1.5" fill="#7af1fc"/>
                </g>
                {{-- Heart pulse line --}}
                <polyline points="50,250 100,250 120,220 140,280 160,210 180,260 220,250 280,250"
                          stroke="#7af1fc" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
                {{-- Mini dots --}}
                <circle cx="200" cy="80" r="4" fill="#fff" opacity="0.6"/>
                <circle cx="280" cy="120" r="3" fill="#fff" opacity="0.5"/>
                <circle cx="350" cy="60" r="5" fill="#7af1fc" opacity="0.7"/>
                <circle cx="120" cy="380" r="6" fill="#fff" opacity="0.4"/>
                <circle cx="400" cy="500" r="4" fill="#7af1fc" opacity="0.6"/>
            </g>
        </svg>
    </div>

    {{-- Content --}}
    <div class="relative z-10 px-margin-mobile md:px-margin-desktop max-w-7xl mx-auto w-full pt-xl pb-32 md:pt-[80px] md:pb-40">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-xs px-md py-sm rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-lg">
                <span class="material-symbols-outlined text-secondary-container text-base" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                <span class="text-label-md text-white/90 font-medium">Akses Mandiri · Aman · Terverifikasi</span>
            </div>

            <h1 class="font-display text-4xl md:text-display-lg text-white mb-md font-extrabold leading-tight tracking-tight">
                Rekam Medis Anda<br>
                <span class="bg-gradient-to-r from-secondary-container to-white bg-clip-text text-transparent">dalam Genggaman</span>
            </h1>
            <p class="text-body-lg text-white/80 max-w-xl leading-relaxed">
                Cek seluruh riwayat kesehatan Anda — kunjungan, kehamilan, persalinan, KB, dan imunisasi anak —
                dengan cepat dan aman menggunakan No. Rekam Medis atau NIK.
            </p>

            {{-- Trust badges --}}
            <div class="flex flex-wrap gap-md mt-lg">
                <div class="flex items-center gap-xs text-white/80 text-body-md">
                    <span class="material-symbols-outlined text-secondary-container">lock</span>
                    Verifikasi 2-Faktor
                </div>
                <div class="flex items-center gap-xs text-white/80 text-body-md">
                    <span class="material-symbols-outlined text-secondary-container">encrypted</span>
                    Data Terenkripsi
                </div>
                <div class="flex items-center gap-xs text-white/80 text-body-md">
                    <span class="material-symbols-outlined text-secondary-container">schedule</span>
                    Akses 24/7
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom wave --}}
    <svg class="absolute bottom-0 left-0 right-0 w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 80" preserveAspectRatio="none" style="height: 60px;">
        <path d="M0,40 C360,80 720,0 1080,40 C1260,60 1440,30 1440,30 L1440,80 L0,80 Z" fill="#f8f9fa"/>
    </svg>
</section>

{{-- ===== Search Card (overlap hero) ===== --}}
<section class="-mt-24 relative z-20 px-margin-mobile md:px-margin-desktop">
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('portal.search') }}" method="POST" class="bg-surface-container-lowest rounded-xl p-lg md:p-xl ambient-shadow-lg border border-outline-variant/50">
            @csrf
            <div class="flex items-start gap-md mb-lg pb-md border-b border-outline-variant/50">
                <div class="w-12 h-12 rounded-xl bg-primary-container/30 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">manage_search</span>
                </div>
                <div class="flex-1">
                    <h2 class="font-display text-headline-md text-on-surface mb-xs">Cari Rekam Medis Anda</h2>
                    <p class="text-body-md text-on-surface-variant">Masukkan No. Rekam Medis atau NIK, kemudian verifikasi dengan tanggal lahir Anda.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-md mb-lg">
                {{-- Identifier --}}
                <div>
                    <label class="block text-label-md uppercase text-on-surface-variant mb-sm font-semibold">
                        <span class="text-primary">●</span> No. Rekam Medis / NIK
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary z-10">badge</span>
                        <input type="text" name="identifier" value="{{ old('identifier') }}" required autocomplete="off"
                               class="w-full h-14 pl-12 pr-4 bg-surface border-2 border-outline-variant rounded-md focus:border-primary focus:outline-none text-body-lg placeholder:text-outline/60 font-medium"
                               placeholder="01-2026-000001 / NIK 16 digit">
                    </div>
                    @error('identifier')<p class="text-error text-label-md mt-xs flex items-center gap-xs"><span class="material-symbols-outlined text-base">error</span>{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal Lahir --}}
                <div>
                    <label class="block text-label-md uppercase text-on-surface-variant mb-sm font-semibold">
                        <span class="text-secondary">●</span> Tanggal Lahir
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-secondary z-10">cake</span>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required max="{{ date('Y-m-d') }}"
                               class="w-full h-14 pl-12 pr-4 bg-surface border-2 border-outline-variant rounded-md focus:border-secondary focus:outline-none text-body-lg font-medium">
                    </div>
                    @error('tanggal_lahir')<p class="text-error text-label-md mt-xs flex items-center gap-xs"><span class="material-symbols-outlined text-base">error</span>{{ $message }}</p>@enderror
                </div>
            </div>

            <button type="submit" class="w-full h-14 px-xl bg-primary text-on-primary text-title-md rounded-md flex items-center justify-center gap-sm hover:bg-primary-container hover:shadow-lg transition-all active:scale-[0.98] font-semibold group">
                <span class="material-symbols-outlined group-hover:rotate-12 transition-transform">search</span>
                Cari Rekam Medis
                <span class="material-symbols-outlined ml-auto opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
            </button>

            <p class="text-label-md text-on-surface-variant text-center mt-md flex items-center justify-center gap-xs">
                <span class="material-symbols-outlined text-base">shield</span>
                Pencarian dibatasi 5x per 15 menit untuk keamanan akun Anda.
            </p>
        </form>
    </div>
</section>

{{-- ===== Feature Cards ===== --}}
<section class="mt-xl px-margin-mobile md:px-margin-desktop">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-lg">
            <span class="text-label-md uppercase font-semibold text-secondary tracking-widest">Keunggulan Portal</span>
            <h2 class="font-display text-headline-md md:text-headline-lg text-on-surface mt-xs">Layanan Rekam Medis Modern</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">

            <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant/50 ambient-shadow hover:-translate-y-1 transition-all group">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center mb-md group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-white text-3xl" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                </div>
                <h3 class="font-display text-title-md mb-xs text-on-surface">Aman & Privat</h3>
                <p class="text-body-md text-on-surface-variant leading-relaxed">
                    Verifikasi 2-faktor No. RM/NIK + Tanggal Lahir. Sesi terenkripsi otomatis berakhir 30 menit.
                </p>
            </div>

            <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant/50 ambient-shadow hover:-translate-y-1 transition-all group">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-secondary to-[#00a3ad] flex items-center justify-center mb-md group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-white text-3xl" style="font-variation-settings: 'FILL' 1;">timeline</span>
                </div>
                <h3 class="font-display text-title-md mb-xs text-on-surface">Riwayat Lengkap</h3>
                <p class="text-body-md text-on-surface-variant leading-relaxed">
                    Timeline kronologis: kunjungan umum, ANC kehamilan, persalinan, KB, neonatus, dan imunisasi anak.
                </p>
            </div>

            <div class="bg-surface-container-lowest rounded-xl p-lg border border-outline-variant/50 ambient-shadow hover:-translate-y-1 transition-all group">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#5e35b1] to-[#7c4dff] flex items-center justify-center mb-md group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-white text-3xl" style="font-variation-settings: 'FILL' 1;">schedule</span>
                </div>
                <h3 class="font-display text-title-md mb-xs text-on-surface">Akses 24/7</h3>
                <p class="text-body-md text-on-surface-variant leading-relaxed">
                    Cek rekam medis kapan saja, di mana saja, tanpa perlu datang ke klinik. Cetak langsung tersedia.
                </p>
            </div>
        </div>

        {{-- Help banner --}}
        <div class="mt-xl bg-gradient-to-r from-primary-container/40 to-secondary-container/40 rounded-xl p-lg border border-primary-container/30">
            <div class="flex items-start gap-md">
                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-primary">support_agent</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-display text-title-md mb-xs text-primary">Tidak punya No. RM atau lupa?</h4>
                    <p class="text-body-md text-on-surface-variant">
                        Hubungi klinik tempat Anda terdaftar untuk konfirmasi data atau bantuan teknis.
                        Format No. Rekam Medis: <code class="bg-white/70 px-sm py-xs rounded text-primary font-mono font-semibold">SS-YYYY-NNNNNN</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
