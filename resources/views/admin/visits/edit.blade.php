@extends('admin.layouts.app')

@section('title', 'Edit Kunjungan')
@section('page_title', 'Edit Kunjungan — '.$visit->no_register)

@section('content')
<form action="{{ route('admin.visits.update', $visit) }}" method="POST">
    @csrf @method('PUT')
    @include('admin.visits._form', ['isEdit' => true])
</form>
@endsection

@push('scripts')
<x-sweet-flash />
@endpush
