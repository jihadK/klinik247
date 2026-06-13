@extends('admin.layouts.app')

@section('title', 'Kunjungan Baru')
@section('page_title', 'Pendaftaran Kunjungan Baru')

@section('content')
<form action="{{ route('admin.visits.store') }}" method="POST">
    @csrf
    @include('admin.visits._form', ['isEdit' => false])
</form>
@endsection

@push('scripts')
<x-sweet-flash />
@endpush
