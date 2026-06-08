@extends('admin.layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
    {{-- Form side --}}
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <div class="w-lg-500px p-10">
                <form method="POST" action="{{ route('admin.login.attempt') }}" class="form w-100" id="kt_sign_in_form">
                    @csrf

                    <div class="text-center mb-11">
                        <h1 class="text-gray-900 fw-bolder mb-3">Klinik247 Admin</h1>
                        <div class="text-gray-500 fw-semibold fs-6">Portal Administrasi Klinik</div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-center mb-8">
                            <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                            <div class="d-flex flex-column">
                                @foreach ($errors->all() as $error)
                                    <span>{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="fv-row mb-8">
                        <label class="form-label fs-6 fw-bolder text-gray-700">Username / Email</label>
                        <input type="text" name="login" placeholder="username atau email"
                               value="{{ old('login') }}" autocomplete="username" autofocus
                               class="form-control bg-transparent" />
                    </div>

                    <div class="fv-row mb-3">
                        <label class="form-label fs-6 fw-bolder text-gray-700">Password</label>
                        <input type="password" name="password" placeholder="Password"
                               autocomplete="current-password"
                               class="form-control bg-transparent" />
                    </div>

                    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="remember" value="1" />
                            <span class="form-check-label text-gray-700">Ingat saya</span>
                        </label>
                        <a href="#" class="link-primary">Lupa password?</a>
                    </div>

                    <div class="d-grid mb-10">
                        <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                            <span class="indicator-label">Sign In</span>
                            <span class="indicator-progress">Mohon tunggu...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>

                    <div class="text-center text-muted fs-7">
                        © {{ date('Y') }} Klinik247 — Multi-tenant Clinic System
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Aside (banner) --}}
    <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2"
         style="background-color: #1e88e5">
        <div class="d-flex flex-column flex-center py-15 px-5 px-md-15 w-100">
            <i class="ki-outline ki-pulse text-white" style="font-size: 6rem"></i>
            <h1 class="text-white fs-2qx fw-bolder text-center mt-5 mb-7">{{ config('app.name') }}</h1>
            <div class="text-white fs-base text-center opacity-75">
                Sistem Manajemen Klinik Multi-Tenant<br/>
                Layanan kesehatan terdigitalisasi untuk klinik kecil & menengah.
            </div>
        </div>
    </div>
</div>
@endsection
