@extends('admin.layouts.app')
@section('title', 'Rekam Medis Pasien')
@section('page_title', 'Rekam Medis Pasien (Integrated)')

@section('content')
<div class="card mb-5">
    <div class="card-header pt-4">
        <h3 class="card-title">
            <i class="ki-outline ki-document fs-2 me-2 text-primary"></i> Pencarian Rekam Medis
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <label class="form-label fs-7 fw-semibold">🔍 Cari Pasien</label>
                <div class="position-relative">
                    <input type="text" name="q" id="rm_search" value="{{ request('q') }}" autofocus autocomplete="off"
                           class="form-control form-control-solid fs-5"
                           placeholder="No. Rekam Medis / NIK / BPJS / No. KK / Nama Pasien / No. HP...">
                    <span id="rm_spinner" class="position-absolute top-50 translate-middle-y end-0 me-3 d-none">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                    </span>

                    {{-- Dropdown suggestion --}}
                    <div id="rm_suggestions" class="position-absolute w-100 bg-body border border-gray-300 rounded shadow d-none"
                         style="top: 100%; left: 0; z-index: 1050; max-height: 450px; overflow-y: auto; margin-top: 2px;"></div>
                </div>
                <div class="form-text fs-8">💡 Ketik min. 2 huruf — saran muncul otomatis. Klik untuk langsung ke rekam medis.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-7 fw-semibold">📅 atau by Tanggal Kunjungan</label>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-solid">
                <div class="form-text fs-8">Pasien yang punya kunjungan di tanggal ini</div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
            </div>
        </form>

        @if(request()->hasAny(['q','date']))
            <hr class="my-4">
            <h4 class="fs-6 mb-3">Hasil Pencarian ({{ $patients->count() }} pasien)</h4>

            @if($patients->isEmpty())
                <div class="text-center py-10 text-muted">
                    <i class="ki-outline ki-search-list fs-3x text-muted opacity-50"></i>
                    <div class="mt-3 fs-5">Pasien tidak ditemukan</div>
                    <div class="fs-7">Coba kata kunci lain atau cek ejaan</div>
                </div>
            @else
                <div class="row g-3">
                    @foreach($patients as $p)
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.rm.show', $p) }}" class="text-decoration-none">
                                <div class="card border h-100 hover-elevate" style="transition: all .2s; cursor:pointer;">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="symbol symbol-50px">
                                                <span class="symbol-label bg-light-primary text-primary fs-2 fw-bolder">{{ mb_substr($p->name, 0, 2) }}</span>
                                            </span>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold fs-5 text-dark">{{ $p->name }}</div>
                                                <div class="text-muted fs-7">
                                                    <span class="badge badge-light-primary">{{ $p->no_rm }}</span>
                                                    · {{ $p->gender_label }} · {{ $p->age }}
                                                </div>
                                                <div class="text-muted fs-8 mt-1">
                                                    @if($p->nik)NIK: {{ $p->nik }}<br>@endif
                                                    @if($p->phone)📞 {{ $p->phone }}<br>@endif
                                                    @if($p->payerType){{ $p->payerType->name }}@endif
                                                </div>
                                            </div>
                                            <i class="ki-outline ki-arrow-right fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <style>.hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }</style>
            @endif
        @else
            <div class="text-center py-10 text-muted">
                <i class="ki-outline ki-search-list fs-3x text-muted opacity-50"></i>
                <div class="mt-3 fs-5">Mulai cari pasien</div>
                <div class="fs-7">Masukkan kata kunci untuk akses rekam medis terintegrasi</div>
            </div>
        @endif
    </div>
</div>
@endsection
@push('scripts')
<x-sweet-flash />
<script>
$(function() {
    const $input = $('#rm_search');
    const $spinner = $('#rm_spinner');
    const $sug = $('#rm_suggestions');
    let timer, lastQuery = '';

    function highlight(text, q) {
        if (! text) return '';
        const safe = String(text).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        if (! q) return safe;
        const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return safe.replace(re, '<mark class="bg-warning bg-opacity-50 px-1 rounded">$1</mark>');
    }

    function doSearch(q) {
        if (q.length < 2) { $sug.addClass('d-none').empty(); return; }
        if (q === lastQuery) return;
        lastQuery = q;

        $spinner.removeClass('d-none');
        $.get('{{ route("admin.rm.ajax.suggest") }}', { q })
            .done(res => {
                $spinner.addClass('d-none');
                const rows = res.data || [];
                if (! rows.length) {
                    $sug.html('<div class="p-4 text-center text-muted fs-7">😕 Tidak ada pasien cocok dengan "<b>' + q + '</b>"</div>').removeClass('d-none');
                    return;
                }
                const html = rows.map(p => `
                    <a href="${p.url}" class="d-flex align-items-center gap-3 p-3 border-bottom text-decoration-none text-dark rm-sug-item">
                        <span class="symbol symbol-40px">
                            <span class="symbol-label bg-light-${p.gender === 'L' ? 'primary' : 'danger'} text-${p.gender === 'L' ? 'primary' : 'danger'} fs-2 fw-bolder">
                                ${p.name.charAt(0).toUpperCase()}
                            </span>
                        </span>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-6">${highlight(p.name, q)}</div>
                            <div class="text-muted fs-8">
                                <span class="badge badge-light-primary fs-9">${highlight(p.no_rm, q)}</span>
                                · ${p.gender === 'L' ? '♂' : '♀'} ${p.age || '-'}
                                ${p.nik ? '· NIK: ' + highlight(p.nik, q) : ''}
                                ${p.phone ? '· 📞 ' + highlight(p.phone, q) : ''}
                                ${p.no_bpjs ? '· BPJS: ' + highlight(p.no_bpjs, q) : ''}
                            </div>
                        </div>
                        <i class="ki-outline ki-arrow-right fs-2 text-primary"></i>
                    </a>
                `).join('');
                $sug.html(html + '<div class="p-2 text-center text-muted fs-9 bg-light">Tekan <kbd>Enter</kbd> untuk lihat semua hasil di bawah</div>').removeClass('d-none');
            })
            .fail(() => {
                $spinner.addClass('d-none');
                $sug.html('<div class="p-3 text-center text-danger fs-7">❌ Gagal cari. Cek koneksi.</div>').removeClass('d-none');
            });
    }

    $input.on('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        timer = setTimeout(() => doSearch(q), 250);
    });

    // Hide on click outside
    $(document).on('click', e => {
        if (! $(e.target).closest('#rm_search, #rm_suggestions').length) {
            $sug.addClass('d-none');
        }
    });

    // Show again on focus if has value
    $input.on('focus', function() {
        if (this.value.trim().length >= 2) {
            lastQuery = ''; // force re-fetch
            doSearch(this.value.trim());
        }
    });

    // Keyboard navigation: down/up arrows
    let cursorIdx = -1;
    $input.on('keydown', function(e) {
        const $items = $('#rm_suggestions .rm-sug-item');
        if (! $items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            cursorIdx = Math.min(cursorIdx + 1, $items.length - 1);
            $items.removeClass('bg-light-primary');
            $items.eq(cursorIdx).addClass('bg-light-primary');
            $items.eq(cursorIdx)[0]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            cursorIdx = Math.max(cursorIdx - 1, 0);
            $items.removeClass('bg-light-primary');
            $items.eq(cursorIdx).addClass('bg-light-primary');
        } else if (e.key === 'Enter' && cursorIdx >= 0) {
            e.preventDefault();
            window.location = $items.eq(cursorIdx).attr('href');
        } else if (e.key === 'Escape') {
            $sug.addClass('d-none');
            cursorIdx = -1;
        }
    });
});
</script>
@endpush
