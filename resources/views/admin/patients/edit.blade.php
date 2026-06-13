@extends('admin.layouts.app')

@section('title', 'Edit Pasien')
@section('page_title', 'Edit Data Pasien — '.$patient->no_rm)

@section('content')
<form action="{{ route('admin.patients.update', $patient) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.patients._form', ['isEdit' => true])
</form>
@endsection

@push('scripts')
<x-sweet-flash />
@endpush
