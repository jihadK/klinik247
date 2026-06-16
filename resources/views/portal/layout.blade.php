<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Pasien') — Klinik247</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/icon-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo/icon-logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#00478d',
                        'on-primary': '#ffffff',
                        'primary-container': '#005eb8',
                        'on-primary-container': '#c8daff',
                        'primary-fixed': '#d6e3ff',
                        'on-primary-fixed': '#001b3d',
                        secondary: '#006970',
                        'on-secondary': '#ffffff',
                        'secondary-container': '#7af1fc',
                        'on-secondary-container': '#006e75',
                        error: '#ba1a1a',
                        'on-error': '#ffffff',
                        'error-container': '#ffdad6',
                        'on-error-container': '#93000a',
                        background: '#f8f9fa',
                        'on-background': '#191c1d',
                        surface: '#f8f9fa',
                        'on-surface': '#191c1d',
                        'on-surface-variant': '#424752',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-low': '#f3f4f5',
                        'surface-container': '#edeeef',
                        'surface-container-high': '#e7e8e9',
                        'surface-variant': '#e1e3e4',
                        outline: '#727783',
                        'outline-variant': '#c2c6d4',
                    },
                    fontFamily: {
                        sans:    ['Inter', 'system-ui', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    borderRadius: {
                        DEFAULT: '0.5rem',
                        sm: '0.25rem',
                        md: '0.75rem',
                        lg: '1rem',
                        xl: '1.5rem',
                    },
                    spacing: {
                        gutter: '24px',
                        xs: '4px',
                        sm: '8px',
                        md: '16px',
                        lg: '24px',
                        xl: '32px',
                        'margin-mobile': '16px',
                        'margin-desktop': '48px',
                    },
                    fontSize: {
                        'display-lg':  ['48px', { lineHeight: '56px', letterSpacing: '-0.02em', fontWeight: '700' }],
                        'headline-lg': ['32px', { lineHeight: '40px', letterSpacing: '-0.01em', fontWeight: '600' }],
                        'headline-md': ['24px', { lineHeight: '32px', fontWeight: '600' }],
                        'title-md':    ['20px', { lineHeight: '28px', fontWeight: '600' }],
                        'body-lg':     ['16px', { lineHeight: '24px', fontWeight: '400' }],
                        'body-md':     ['14px', { lineHeight: '20px', fontWeight: '400' }],
                        'label-md':    ['12px', { lineHeight: '16px', letterSpacing: '0.05em', fontWeight: '500' }],
                    },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Plus Jakarta Sans', Inter, system-ui, sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .ambient-shadow      { box-shadow: 0 16px 40px -12px rgba(0, 71, 141, 0.18), 0 4px 12px -4px rgba(0, 71, 141, 0.08); }
        .ambient-shadow-lg   { box-shadow: 0 32px 80px -20px rgba(0, 71, 141, 0.28), 0 8px 24px -6px rgba(0, 71, 141, 0.12); }
        .glow-primary        { box-shadow: 0 0 0 6px rgba(0, 94, 184, 0.10), 0 0 0 12px rgba(0, 94, 184, 0.04); }
        .timeline-line::before {
            content: ''; position: absolute; left: 11px; top: 32px; bottom: -28px;
            width: 2px; background: linear-gradient(to bottom, #c8daff, #c2c6d4 50%, transparent);
        }
        .timeline-item:last-child .timeline-line::before { display: none; }

        /* Hero overlay patterns */
        .hero-grid {
            background-image:
                linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .hero-radial {
            background: radial-gradient(circle at 20% 30%, rgba(122, 241, 252, 0.35) 0%, transparent 45%),
                        radial-gradient(circle at 80% 70%, rgba(214, 227, 255, 0.30) 0%, transparent 50%),
                        radial-gradient(circle at 50% 100%, rgba(0, 105, 112, 0.30) 0%, transparent 55%);
        }

        /* Float anim utk badge logo */
        @keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        .floaty { animation: floaty 4s ease-in-out infinite; }

        /* Pulse ring untuk verified indicator */
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(0, 105, 112, 0.5); }
            70%  { box-shadow: 0 0 0 12px rgba(0, 105, 112, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 105, 112, 0); }
        }
        .pulse-dot { animation: pulse-ring 2s infinite; }

        /* Smooth input transitions */
        input[type="text"], input[type="date"] {
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
        input[type="text"]:focus, input[type="date"]:focus {
            box-shadow: 0 0 0 4px rgba(0, 94, 184, 0.10);
        }

        /* Print-friendly */
        @media print {
            header, footer, .no-print { display: none !important; }
            .ambient-shadow, .ambient-shadow-lg { box-shadow: none !important; }
        }
    </style>
    @stack('head')
</head>
<body class="bg-background text-on-background overflow-x-hidden">

    {{-- ===== Top App Bar ===== --}}
    <header class="bg-surface-container-lowest/95 backdrop-blur-md sticky top-0 z-50 flex justify-between items-center w-full px-margin-mobile md:px-margin-desktop h-20 border-b border-outline-variant/60">
        <a href="{{ route('portal.index') }}" class="flex items-center gap-md group">
            <img src="{{ asset('assets/logo/logo-klinik-247-h.png') }}" alt="Klinik247"
                 class="h-12 w-auto object-contain group-hover:scale-105 transition-transform">
            <span class="hidden md:inline-block text-label-md text-on-surface-variant border-l border-outline-variant pl-md ml-xs">
                Portal Pasien
            </span>
        </a>

        <div class="flex items-center gap-sm">
            @if(session('portal_patient_id'))
                <div class="hidden md:flex items-center gap-xs px-md py-sm rounded-full bg-secondary-container/60 text-on-secondary-container text-label-md font-medium">
                    <span class="w-2 h-2 rounded-full bg-secondary pulse-dot"></span>
                    Sesi Aktif
                </div>
                <form action="{{ route('portal.logout') }}" method="POST">
                    @csrf
                    <button class="flex items-center gap-xs px-md py-sm rounded-full bg-error-container text-on-error-container text-label-md font-medium hover:bg-error hover:text-on-error transition active:scale-95">
                        <span class="material-symbols-outlined text-base">logout</span>
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('admin.login') }}" class="flex items-center gap-xs px-md py-sm rounded-full bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-primary hover:text-on-primary transition active:scale-95">
                    <span class="material-symbols-outlined text-base">badge</span>
                    Petugas Login
                </a>
            @endif
        </div>
    </header>

    {{-- ===== Flash Messages ===== --}}
    @if(session('flash_error') || session('flash_success'))
        <div class="px-margin-mobile md:px-margin-desktop mt-md max-w-7xl mx-auto">
            @if(session('flash_error'))
                <div class="bg-error-container text-on-error-container px-lg py-md rounded-lg flex items-center gap-sm">
                    <span class="material-symbols-outlined">error</span>
                    <span class="text-body-md font-medium">{{ session('flash_error') }}</span>
                </div>
            @endif
            @if(session('flash_success'))
                <div class="bg-secondary-container text-on-secondary-container px-lg py-md rounded-lg flex items-center gap-sm">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span class="text-body-md font-medium">{{ session('flash_success') }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- ===== Main ===== --}}
    <main class="min-h-screen pb-xl">
        @yield('content')
    </main>

    {{-- ===== Footer ===== --}}
    <footer class="w-full py-lg px-margin-mobile md:px-margin-desktop flex flex-col md:flex-row justify-between items-center gap-md border-t border-outline-variant bg-surface">
        <div class="flex flex-col md:flex-row items-center gap-md">
            <span class="text-label-md font-semibold text-primary">Klinik247 — Portal Pasien</span>
            <span class="text-label-md text-on-surface-variant">© {{ date('Y') }} Klinik247. All rights reserved.</span>
        </div>
        <div class="flex gap-md">
            <a href="#" class="text-label-md text-on-surface-variant hover:text-primary transition cursor-pointer">Kebijakan Privasi</a>
            <a href="#" class="text-label-md text-on-surface-variant hover:text-primary transition cursor-pointer">Syarat & Ketentuan</a>
            <a href="#" class="text-label-md text-on-surface-variant hover:text-primary transition cursor-pointer">Bantuan</a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
