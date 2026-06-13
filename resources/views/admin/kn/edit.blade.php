@extends('admin.layouts.app')
@section('title', 'Edit Bayi')
@section('page_title', 'Edit Data Bayi — '.$neonate->nama_bayi)

@section('content')
<form action="{{ route('admin.kn.update', $neonate) }}" method="POST">
    @csrf @method('PUT')

    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">Identitas Bayi</h3></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fs-7 required">Nama Bayi</label>
                    <input type="text" name="nama_bayi" value="{{ old('nama_bayi', $neonate->nama_bayi) }}" class="form-control form-control-solid" required>
                </div>
                <div class="col-md-3"><label class="form-label fs-7">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select form-select-solid">
                        <option value="">-</option>
                        <option value="L" @selected($neonate->jenis_kelamin === 'L')>Laki-laki</option>
                        <option value="P" @selected($neonate->jenis_kelamin === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label fs-7">Status</label>
                    <select name="status" class="form-select form-select-solid">
                        @foreach($statuses as $k => $s)<option value="{{ $k }}" @selected($neonate->status === $k)>{{ $s['label'] }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label fs-7">Tgl Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ optional($neonate->tanggal_lahir)->format('Y-m-d') }}" class="form-control form-control-solid">
                </div>
                <div class="col-md-3"><label class="form-label fs-7">Jam Lahir</label>
                    <input type="time" name="jam_lahir" value="{{ $neonate->jam_lahir }}" class="form-control form-control-solid">
                </div>
                <div class="col-md-3"><label class="form-label fs-7">BB Lahir (gr)</label>
                    <input type="number" name="bb_lahir_gram" value="{{ $neonate->bb_lahir_gram }}" class="form-control form-control-solid">
                </div>
                <div class="col-md-3"><label class="form-label fs-7">PB Lahir (cm)</label>
                    <input type="number" step="0.1" name="pb_lahir_cm" value="{{ $neonate->pb_lahir_cm }}" class="form-control form-control-solid">
                </div>
                <div class="col-md-3"><label class="form-label fs-7">APGAR 1'</label>
                    <input type="number" name="apgar_1" min="0" max="10" value="{{ $neonate->apgar_1 }}" class="form-control form-control-solid">
                </div>
                <div class="col-md-3"><label class="form-label fs-7">APGAR 5'</label>
                    <input type="number" name="apgar_5" min="0" max="10" value="{{ $neonate->apgar_5 }}" class="form-control form-control-solid">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">💉 Tindakan Saat Lahir</h3></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="form-check form-check-custom p-2 border rounded">
                        <input class="form-check-input" type="checkbox" name="imd_dilakukan" value="1" id="imd" @checked($neonate->imd_dilakukan)>
                        <label class="form-check-label ms-2" for="imd">IMD (Inisiasi Menyusu Dini)</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-check-custom p-2 border rounded">
                        <input class="form-check-input" type="checkbox" name="vit_k1_diberi" value="1" id="vitk" @checked($neonate->vit_k1_diberi)>
                        <label class="form-check-label ms-2" for="vitk">Vit K1 Injeksi</label>
                    </div>
                    <input type="datetime-local" name="vit_k1_at" value="{{ optional($neonate->vit_k1_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-sm form-control-solid mt-1">
                </div>
                <div class="col-md-3">
                    <div class="form-check form-check-custom p-2 border rounded">
                        <input class="form-check-input" type="checkbox" name="salep_mata" value="1" id="sm" @checked($neonate->salep_mata)>
                        <label class="form-check-label ms-2" for="sm">Salep Mata</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-check-custom p-2 border rounded">
                        <input class="form-check-input" type="checkbox" name="hb0_diberi" value="1" id="hb0" @checked($neonate->hb0_diberi)>
                        <label class="form-check-label ms-2" for="hb0">HB-0</label>
                    </div>
                    <input type="datetime-local" name="hb0_at" value="{{ optional($neonate->hb0_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-sm form-control-solid mt-1">
                    <input type="text" name="hb0_batch" value="{{ $neonate->hb0_batch }}" placeholder="No. Batch" class="form-control form-control-sm form-control-solid mt-1">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <label class="form-label fs-7">Keterangan Akhir</label>
            <textarea name="keterangan_akhir" rows="2" class="form-control form-control-solid">{{ $neonate->keterangan_akhir }}</textarea>
            <label class="form-label fs-7 mt-3">Catatan</label>
            <textarea name="notes" rows="2" class="form-control form-control-solid">{{ $neonate->notes }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('admin.kn.show', $neonate) }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary">💾 Update Bayi</button>
    </div>
</form>
@endsection
@push('scripts')<x-sweet-flash />@endpush
