@extends('portal.layout')
@section('title', 'Rekam Medis '.$patient->name)

@section('content')

{{-- ===== Hero Banner mini ===== --}}
<section class="relative w-full overflow-hidden" style="min-height: 220px;">
    <div class="absolute inset-0 bg-gradient-to-br from-[#001b3d] via-primary to-primary-container"></div>
    <div class="absolute inset-0 hero-radial opacity-80"></div>
    <div class="absolute inset-0 hero-grid opacity-30"></div>

    {{-- Decorative pattern right --}}
    <svg class="absolute right-0 top-0 h-full opacity-20 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 240" preserveAspectRatio="xMidYMid slice">
        <circle cx="320" cy="100" r="120" fill="#7af1fc" opacity="0.3"/>
        <circle cx="320" cy="100" r="70" fill="#fff" opacity="0.15"/>
        <polyline points="20,160 80,160 100,130 120,190 140,120 160,170 200,160"
                  stroke="#7af1fc" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0.7"/>
    </svg>

    <div class="relative z-10 px-margin-mobile md:px-margin-desktop max-w-7xl mx-auto w-full py-xl">
        <div class="flex items-center gap-xs px-md py-sm rounded-full bg-secondary-container/30 backdrop-blur-sm border border-secondary-container/40 w-fit mb-md">
            <span class="w-2 h-2 rounded-full bg-secondary-container pulse-dot"></span>
            <span class="text-label-md text-secondary-container font-medium">
                Terverifikasi {{ \Carbon\Carbon::parse($verified_at)->diffForHumans() }}
            </span>
        </div>
        <h1 class="font-display text-headline-lg md:text-display-lg text-white font-extrabold leading-tight">
            Halo, {{ \Illuminate\Support\Str::words($patient->name, 1, '') }} 👋
        </h1>
        <p class="text-body-lg text-white/80 mt-xs max-w-2xl">
            Berikut riwayat rekam medis Anda di {{ $patient->site?->name ?? 'klinik' }}.
        </p>
    </div>

    {{-- Bottom wave --}}
    <svg class="absolute bottom-0 left-0 right-0 w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none" style="height: 40px;">
        <path d="M0,30 C360,60 720,0 1080,30 C1260,45 1440,20 1440,20 L1440,60 L0,60 Z" fill="#f8f9fa"/>
    </svg>
</section>

<section class="px-margin-mobile md:px-margin-desktop mt-md">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-lg">

        {{-- ===== Sidebar — Patient Profile ===== --}}
        <aside class="lg:col-span-4 lg:sticky lg:top-24 h-fit">
            <div class="bg-surface-container-lowest rounded-xl overflow-hidden border border-outline-variant/50 ambient-shadow">

                {{-- Profile header dengan gradient --}}
                <div class="relative bg-gradient-to-br from-primary-container/40 via-secondary-container/30 to-surface-container-lowest p-lg pb-md">
                    <div class="flex items-center gap-md">
                        <div class="relative">
                            <div class="w-20 h-20 rounded-2xl bg-white shadow-md flex items-center justify-center floaty">
                                <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">
                                    {{ $patient->gender === 'P' ? 'face_3' : 'face_6' }}
                                </span>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-secondary border-2 border-white flex items-center justify-center">
                                <span class="material-symbols-outlined text-white" style="font-size: 16px;">verified</span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-display text-title-md text-on-surface truncate font-bold">{{ $patient->name }}</h2>
                            <span class="inline-flex items-center gap-xs bg-primary text-on-primary px-sm py-xs rounded-md text-label-md font-mono font-semibold mt-xs">
                                <span class="material-symbols-outlined text-xs" style="font-size:14px;">qr_code_2</span>
                                {{ $patient->no_rm }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Info grid --}}
                <div class="p-lg pt-md space-y-md">

                    <div class="grid grid-cols-2 gap-sm">
                        <div class="bg-surface-container-low rounded-md p-sm">
                            <p class="text-label-md uppercase text-on-surface-variant mb-xs">L/P</p>
                            <p class="text-body-md font-bold text-on-surface flex items-center gap-xs">
                                <span class="material-symbols-outlined text-base text-primary">{{ $patient->gender === 'P' ? 'female' : 'male' }}</span>
                                {{ $patient->gender_label }}
                            </p>
                        </div>
                        <div class="bg-surface-container-low rounded-md p-sm">
                            <p class="text-label-md uppercase text-on-surface-variant mb-xs">Umur</p>
                            <p class="text-body-md font-bold text-on-surface flex items-center gap-xs">
                                <span class="material-symbols-outlined text-base text-secondary">cake</span>
                                {{ $patient->age }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-sm">
                        <div class="flex justify-between items-center py-xs border-b border-surface-variant">
                            <span class="text-on-surface-variant text-label-md uppercase flex items-center gap-xs">
                                <span class="material-symbols-outlined text-base">event</span> Tgl Lahir
                            </span>
                            <span class="text-body-md text-on-surface font-semibold">{{ optional($patient->birth_date)->isoFormat('D MMM YYYY') }}</span>
                        </div>
                        @if($patient->phone)
                            <div class="flex justify-between items-center py-xs border-b border-surface-variant">
                                <span class="text-on-surface-variant text-label-md uppercase flex items-center gap-xs">
                                    <span class="material-symbols-outlined text-base">call</span> HP
                                </span>
                                <span class="text-body-md text-on-surface font-semibold">{{ $patient->phone }}</span>
                            </div>
                        @endif
                        @if($patient->payerType)
                            <div class="flex justify-between items-center py-xs border-b border-surface-variant">
                                <span class="text-on-surface-variant text-label-md uppercase flex items-center gap-xs">
                                    <span class="material-symbols-outlined text-base">credit_card</span> Pembiayaan
                                </span>
                                <span class="text-body-md text-on-surface font-semibold">{{ $patient->payerType->name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center py-xs">
                            <span class="text-on-surface-variant text-label-md uppercase flex items-center gap-xs">
                                <span class="material-symbols-outlined text-base">local_hospital</span> Klinik
                            </span>
                            <span class="text-body-md text-on-surface font-semibold text-right">{{ $patient->site?->name }}</span>
                        </div>
                    </div>

                    @if($patient->full_address)
                        <div class="bg-surface-container-low rounded-md p-sm">
                            <p class="text-on-surface-variant text-label-md uppercase mb-xs flex items-center gap-xs">
                                <span class="material-symbols-outlined text-base">location_on</span> Alamat
                            </p>
                            <p class="text-body-md text-on-surface leading-relaxed">{{ \Illuminate\Support\Str::limit($patient->full_address, 80) }}</p>
                        </div>
                    @endif

                    {{-- ALERGI (highlight) --}}
                    @if($patient->allergies)
                        <div class="p-md bg-error-container rounded-md border-l-4 border-error">
                            <p class="text-on-error-container text-label-md uppercase mb-xs font-bold flex items-center gap-xs">
                                <span class="material-symbols-outlined text-base">warning</span> Alergi
                            </p>
                            <p class="text-body-md text-on-error-container font-semibold">{{ $patient->allergies }}</p>
                        </div>
                    @endif
                    @if($patient->chronic_diseases)
                        <div class="p-md bg-secondary-container rounded-md border-l-4 border-secondary">
                            <p class="text-on-secondary-container text-label-md uppercase mb-xs font-bold flex items-center gap-xs">
                                <span class="material-symbols-outlined text-base">monitor_heart</span> Penyakit Kronis
                            </p>
                            <p class="text-body-md text-on-secondary-container">{{ $patient->chronic_diseases }}</p>
                        </div>
                    @endif

                    <form action="{{ route('portal.logout') }}" method="POST" class="pt-sm">
                        @csrf
                        <button type="submit" class="w-full py-md border-2 border-primary text-primary text-body-md rounded-md hover:bg-primary hover:text-on-primary transition-all active:scale-[0.98] font-semibold flex items-center justify-center gap-xs">
                            <span class="material-symbols-outlined">logout</span>
                            Keluar Portal
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ===== Timeline (right column) ===== --}}
        <div class="lg:col-span-8 space-y-lg">

            {{-- Stats row --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-sm no-print">
                @php
                    $totalEvents = $timeline->count();
                    $thisYear    = $timeline->filter(fn($t) => $t['date'] && $t['date']->year === (int) date('Y'))->count();
                    $lastVisit   = $timeline->first()['date'] ?? null;
                    $clinicCount = 1;
                @endphp
                <div class="bg-surface-container-lowest rounded-lg p-md border border-outline-variant/50 ambient-shadow">
                    <div class="flex items-center justify-between mb-xs">
                        <span class="text-label-md uppercase text-on-surface-variant">Total Event</span>
                        <span class="material-symbols-outlined text-primary text-base">database</span>
                    </div>
                    <p class="font-display text-2xl font-bold text-primary">{{ $totalEvents }}</p>
                </div>
                <div class="bg-surface-container-lowest rounded-lg p-md border border-outline-variant/50 ambient-shadow">
                    <div class="flex items-center justify-between mb-xs">
                        <span class="text-label-md uppercase text-on-surface-variant">Tahun Ini</span>
                        <span class="material-symbols-outlined text-secondary text-base">calendar_month</span>
                    </div>
                    <p class="font-display text-2xl font-bold text-secondary">{{ $thisYear }}</p>
                </div>
                <div class="bg-surface-container-lowest rounded-lg p-md border border-outline-variant/50 ambient-shadow">
                    <div class="flex items-center justify-between mb-xs">
                        <span class="text-label-md uppercase text-on-surface-variant">Kunjungan Terakhir</span>
                        <span class="material-symbols-outlined text-primary text-base">event_available</span>
                    </div>
                    <p class="font-display text-sm font-bold text-on-surface">
                        {{ $lastVisit ? $lastVisit->isoFormat('D MMM YY') : '—' }}
                    </p>
                </div>
                <div class="bg-surface-container-lowest rounded-lg p-md border border-outline-variant/50 ambient-shadow">
                    <div class="flex items-center justify-between mb-xs">
                        <span class="text-label-md uppercase text-on-surface-variant">Klinik</span>
                        <span class="material-symbols-outlined text-secondary text-base">local_hospital</span>
                    </div>
                    <p class="font-display text-sm font-bold text-on-surface">{{ $patient->site?->code ?? '—' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-display text-headline-md text-on-surface font-bold">Riwayat Pelayanan</h3>
                    <p class="text-body-md text-on-surface-variant">Urut dari yang terbaru</p>
                </div>
                <div class="flex gap-sm no-print">
                    <button onclick="window.print()" class="flex items-center gap-xs px-md py-sm bg-primary text-on-primary rounded-full text-label-md font-semibold hover:bg-primary-container transition-all active:scale-95">
                        <span class="material-symbols-outlined text-base">print</span> Cetak
                    </button>
                </div>
            </div>

            @if($timeline->isEmpty())
                <div class="bg-surface-container-lowest rounded-xl p-xl border border-outline-variant/50 ambient-shadow text-center">
                    <div class="w-20 h-20 rounded-full bg-surface-container mx-auto flex items-center justify-center mb-md">
                        <span class="material-symbols-outlined text-outline" style="font-size: 48px;">history</span>
                    </div>
                    <h4 class="font-display text-title-md mb-xs">Belum Ada Riwayat</h4>
                    <p class="text-body-md text-on-surface-variant">Anda belum memiliki riwayat pelayanan di klinik ini.</p>
                </div>
            @else
                <div class="space-y-gutter">
                    @foreach($timeline as $t)
                        @php
                            $color = $t['tag_color'] === 'error' ? 'error' : ($t['tag_color'] === 'primary' ? 'primary' : 'secondary');
                            $bgClass = $color === 'error' ? 'bg-error' : ($color === 'primary' ? 'bg-primary' : 'bg-secondary');
                        @endphp
                        <div class="timeline-item flex gap-md relative">
                            <div class="timeline-line relative flex flex-col items-center pt-md flex-shrink-0">
                                <div class="w-6 h-6 rounded-full {{ $bgClass }} border-4 border-background shadow-md z-10 flex items-center justify-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </div>
                            </div>
                            <div class="flex-grow bg-surface-container-lowest p-lg rounded-xl border border-outline-variant/50 ambient-shadow hover:border-primary hover:-translate-y-0.5 transition-all">
                                <div class="flex flex-col md:flex-row md:items-start justify-between mb-md gap-sm">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-sm mb-xs flex-wrap">
                                            <span class="font-display text-title-md text-primary font-bold">{{ $t['date']->isoFormat('D MMM YYYY') }}</span>
                                            @if($t['date']->format('H:i') !== '00:00')
                                                <span class="text-outline-variant">•</span>
                                                <span class="text-body-md text-on-surface-variant font-mono">{{ $t['date']->isoFormat('HH:mm') }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-sm flex-wrap">
                                            <span class="text-body-lg font-semibold text-on-surface">{{ $t['title'] }}</span>
                                            @if($t['meta'])
                                                <span class="text-outline-variant">•</span>
                                                <span class="text-body-md text-on-surface-variant font-mono bg-surface-container px-sm py-xs rounded">{{ $t['meta'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($t['tag'])
                                        <span class="inline-flex items-center gap-xs px-md py-xs rounded-full
                                            @if($color === 'error') bg-error-container text-on-error-container
                                            @elseif($color === 'primary') bg-primary-container/40 text-primary
                                            @else bg-secondary-container text-on-secondary-container
                                            @endif
                                            text-label-md font-semibold whitespace-nowrap">
                                            <span class="w-2 h-2 rounded-full {{ $bgClass }}"></span>
                                            {{ $t['tag'] }}
                                        </span>
                                    @endif
                                </div>

                                @if(! empty($t['sections']))
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-md pt-md border-t border-surface-variant">
                                        @foreach($t['sections'] as $label => $value)
                                            <div>
                                                <p class="text-on-surface-variant text-label-md uppercase mb-xs font-medium">{{ $label }}</p>
                                                <p class="text-body-md text-on-surface font-semibold">{{ $value }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

@endsection
