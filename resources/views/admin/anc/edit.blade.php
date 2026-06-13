@extends('admin.layouts.app')
@section('title', 'Edit Kehamilan')
@section('page_title', 'Edit Kehamilan — '.$pregnancy->no_kartu_hamil)

@section('content')
<form action="{{ route('admin.anc.update', $pregnancy) }}" method="POST">
    @csrf @method('PUT')
    @include('admin.anc._form', ['isEdit' => true])
</form>
@endsection

@push('scripts')<x-sweet-flash />@endpush
