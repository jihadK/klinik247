@extends('admin.layouts.app')

@section('title', 'Pasien Baru')
@section('page_title', 'Pendaftaran Pasien Baru')

@section('content')
<form action="{{ route('admin.patients.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.patients._form', ['isEdit' => false])
</form>
@endsection

@push('scripts')
<x-sweet-flash />
@endpush
