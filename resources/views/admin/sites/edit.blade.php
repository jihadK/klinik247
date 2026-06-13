@extends('admin.layouts.app')
@section('title', 'Edit Klinik — '.$site->name)
@section('page_title', 'Pengaturan Klinik — '.$site->name)

@section('content')
<form action="{{ route('admin.sites.update', $site) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row">
        <div class="col-md-8">
            {{-- Info Dasar --}}
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">📋 Informasi Dasar</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fs-7">Kode</label>
                            <input type="text" value="{{ $site->code }}" class="form-control form-control-solid" readonly>
                            <div class="form-text fs-9">Kode tidak bisa diubah</div>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fs-7 required">Nama Klinik</label>
                            <input type="text" name="name" value="{{ old('name', $site->name) }}" class="form-control form-control-solid @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-9">
                            <label class="form-label fs-7">Alamat</label>
                            <input type="text" name="address" value="{{ old('address', $site->address) }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7">Kota</label>
                            <input type="text" name="city" value="{{ old('city', $site->city) }}" class="form-control form-control-solid">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-7">No. Telpon</label>
                            <input type="text" name="phone" value="{{ old('phone', $site->phone) }}" class="form-control form-control-solid" placeholder="Mis. 0851-1234-5678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7">Email</label>
                            <input type="email" name="email" value="{{ old('email', $site->email) }}" class="form-control form-control-solid">
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch form-check-custom">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $site->is_active))>
                                <label class="form-check-label fw-semibold ms-3" for="is_active">Klinik Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Letterhead Settings --}}
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">📜 Pengaturan Surat (Letterhead)</h3></div>
                <div class="card-body">
                    <div class="alert alert-info py-2 fs-8 mb-3">
                        <i class="ki-outline ki-information-5 fs-3 me-1"></i>
                        Data ini dipakai untuk <b>Kop Surat</b> di Surat Rujukan, Kartu Pasien, Kartu KB, Asuhan Persalinan, Resep, dst.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fs-7">Subtitle</label>
                            <input type="text" name="letterhead_subtitle" value="{{ old('letterhead_subtitle', $site->letterhead_subtitle) }}" class="form-control form-control-solid" placeholder="Mis. Praktik Mandiri Bidan (PMB)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7">Nama Bidan PJ (Default Signer)</label>
                            <input type="text" name="letterhead_director" value="{{ old('letterhead_director', $site->letterhead_director) }}" class="form-control form-control-solid" placeholder="Mis. I'annatus Sa'diyah, A.Md.Keb">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7">Nomor SIPB / NIP</label>
                            <input type="text" name="letterhead_sipb" value="{{ old('letterhead_sipb', $site->letterhead_sipb) }}" class="form-control form-control-solid" placeholder="Mis. SIPB/PMB-001/2024 — kosongkan kalau tidak punya">
                            <div class="form-text fs-9">Jika kosong, tidak akan ditampilkan di surat.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7">Kota Penetapan Surat</label>
                            <input type="text" name="letterhead_city" value="{{ old('letterhead_city', $site->letterhead_city ?? $site->city) }}" class="form-control form-control-solid" placeholder="Mis. Lamongan">
                            <div class="form-text fs-9">Akan muncul di "Lamongan, 13 Juni 2026"</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: Logo + Kop Image --}}
        <div class="col-md-4">
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">🖼 Logo Klinik</h3></div>
                <div class="card-body text-center">
                    @if($site->logo_url)
                        <img src="{{ asset('storage/'.$site->logo_url) }}" alt="logo" style="max-height:150px; max-width:100%; object-fit:contain;" class="mb-3">
                        <form action="{{ route('admin.sites.logo.destroy', $site) }}" method="POST" class="d-inline form-del-asset">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light-danger w-100 mb-3"><i class="ki-outline ki-trash fs-3"></i> Hapus Logo</button>
                        </form>
                    @else
                        <div class="text-muted py-5">Belum ada logo</div>
                    @endif
                    <label class="form-label fs-7">Upload Logo Baru</label>
                    <input type="file" name="logo" accept="image/*" class="form-control form-control-solid">
                    <div class="form-text fs-9">JPG/PNG/WebP/SVG max 1 MB · ukuran ideal 200×200px</div>
                </div>
            </div>

            <div class="card mb-5 border border-primary">
                <div class="card-header bg-light-primary"><h3 class="card-title text-primary">📄 Kop Surat (Image)</h3></div>
                <div class="card-body text-center">
                    @if($site->kop_image_url)
                        <img src="{{ asset('storage/'.$site->kop_image_url) }}" alt="kop" style="max-width:100%; object-fit:contain;" class="border mb-3">
                        <form action="{{ route('admin.sites.kop.destroy', $site) }}" method="POST" class="d-inline form-del-asset">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light-danger w-100 mb-3"><i class="ki-outline ki-trash fs-3"></i> Hapus Kop</button>
                        </form>
                    @else
                        <div class="text-muted py-5">Belum upload kop surat</div>
                    @endif
                    <label class="form-label fs-7">Upload Kop Surat</label>
                    <input type="file" name="kop_image" accept="image/jpeg,image/png" class="form-control form-control-solid">
                    <div class="form-text fs-9">JPG/PNG max 2 MB · ukuran ideal 2100×400px (landscape banner)</div>
                    <div class="alert alert-warning fs-9 mt-2 py-2">
                        💡 Kalau kop surat di-upload, akan dipakai sebagai header di Surat Rujukan & dokumen lain (override text-only kop).
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('admin.sites.index') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary">
            <i class="ki-outline ki-check fs-3"></i> Simpan Pengaturan
        </button>
    </div>
</form>
@endsection

@push('scripts')
<x-sweet-flash />
<x-sweet-helpers />
<script>
$(function() {
    $('.form-del-asset').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus file?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' }
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
