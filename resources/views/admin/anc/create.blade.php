@extends('admin.layouts.app')
@section('title', 'Pendaftaran Kehamilan (K1)')
@section('page_title', 'Pendaftaran Kehamilan — Kunjungan Pertama')

@section('content')
<form action="{{ route('admin.anc.store') }}" method="POST">
    @csrf
    @include('admin.anc._form', ['isEdit' => false])
</form>
@endsection

@push('scripts')<x-sweet-flash />@endpush
