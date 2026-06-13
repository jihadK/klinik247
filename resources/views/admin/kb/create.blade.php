@extends('admin.layouts.app')
@section('title', 'Akseptor KB Baru')
@section('page_title', 'Pendaftaran Akseptor KB')

@section('content')
<form action="{{ route('admin.kb.store') }}" method="POST">
    @csrf
    @include('admin.kb._form', ['isEdit' => false])
</form>
@endsection

@push('scripts')<x-sweet-flash />@endpush
