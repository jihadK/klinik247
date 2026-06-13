@extends('admin.layouts.app')
@section('title', 'Edit Akseptor KB')
@section('page_title', 'Edit Akseptor — '.$acceptor->no_kartu_kb)

@section('content')
<form action="{{ route('admin.kb.update', $acceptor) }}" method="POST">
    @csrf @method('PUT')
    @include('admin.kb._form', ['isEdit' => true])
</form>
@endsection

@push('scripts')<x-sweet-flash />@endpush
