<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AncController;
use App\Http\Controllers\Admin\ChildController;
use App\Http\Controllers\Admin\IncController;
use App\Http\Controllers\Admin\KbController;
use App\Http\Controllers\Admin\KnController;
use App\Http\Controllers\Admin\MedicalRecordController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PatientVisitController;
use App\Http\Controllers\Admin\PncController;
use App\Http\Controllers\Admin\SiteController;
use Illuminate\Support\Facades\Route;

// ===== Root: redirect ke portal pasien =====
Route::get('/', function () {
    return redirect()->route('portal.index');
})->name('home');

// ===== Customer Portal (PUBLIC - no auth) =====
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/',         [\App\Http\Controllers\Portal\PortalController::class, 'index'])->name('index');
    Route::post('/search',  [\App\Http\Controllers\Portal\PortalController::class, 'search'])->name('search');
    Route::get('/result',   [\App\Http\Controllers\Portal\PortalController::class, 'result'])->name('result');
    Route::post('/logout',  [\App\Http\Controllers\Portal\PortalController::class, 'logout'])->name('logout');
});

// ===== Portal customer (placeholder Phase berikutnya) =====
// Route::get('/portal', [...])->name('portal.home');

// ===== Admin Portal =====
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest
    Route::middleware('guest')->group(function () {
        Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    });

    // Authenticated
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/',          [DashboardController::class, 'index'])->name('home');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ===== Pendaftaran / Pasien =====
        Route::middleware('permission:patients.view')->group(function () {
            // AJAX cascade wilayah — declare BEFORE resource supaya tidak ditangkap {patient}
            Route::get('/patients/ajax/lookup-kk',  [PatientController::class, 'ajaxLookupKk'])->name('patients.ajax.lookup-kk');
            Route::get('/patients/ajax/suggest-kk', [PatientController::class, 'ajaxSuggestKk'])->name('patients.ajax.suggest-kk');
            Route::get('/patients/ajax/regencies', [PatientController::class, 'ajaxRegencies'])->name('patients.ajax.regencies');
            Route::get('/patients/ajax/districts', [PatientController::class, 'ajaxDistricts'])->name('patients.ajax.districts');
            Route::get('/patients/ajax/villages',  [PatientController::class, 'ajaxVillages'])->name('patients.ajax.villages');

            Route::get('/patients/{patient}/kartu', [PatientController::class, 'kartu'])
                ->whereNumber('patient')
                ->name('patients.kartu');

            Route::resource('patients', PatientController::class)
                ->parameters(['patients' => 'patient'])
                ->whereNumber('patient');
        });

        // ===== Pendaftaran / Kunjungan =====
        Route::middleware('permission:visits.view')->group(function () {
            // AJAX search pasien (static path duluan, sebelum {visit})
            Route::get('/visits/ajax/search-patient', [PatientVisitController::class, 'searchPatient'])->name('visits.ajax.search-patient');
            Route::get('/visits/ajax/check-history',  [PatientVisitController::class, 'ajaxCheckHistory'])->name('visits.ajax.check-history');
            // Quick status update
            Route::post('/visits/{visit}/set-status', [PatientVisitController::class, 'setStatus'])
                ->whereNumber('visit')->name('visits.set-status');

            Route::resource('visits', PatientVisitController::class)
                ->parameters(['visits' => 'visit'])
                ->whereNumber('visit');
        });

        // ===== Pelayanan KB =====
        Route::middleware('permission:kb.view')->group(function () {
            Route::get('/kb/{kb}/kartu', [KbController::class, 'kartu'])
                ->whereNumber('kb')->name('kb.kartu');

            Route::post('/kb/{kb}/visit', [KbController::class, 'storeVisit'])
                ->whereNumber('kb')->name('kb.visit.store');

            Route::post('/kb/{kb}/ganti-alat', [KbController::class, 'gantiAlat'])
                ->whereNumber('kb')->name('kb.ganti-alat');

            Route::delete('/kb/visits/{visit}', [KbController::class, 'destroyVisit'])
                ->whereNumber('visit')->name('kb.visit.destroy');

            Route::resource('kb', KbController::class)
                ->parameters(['kb' => 'kb'])
                ->whereNumber('kb');
        });

        // ===== Pelayanan ANC (Ibu Hamil) =====
        Route::middleware('permission:anc.view')->group(function () {
            // AJAX (static path duluan)
            Route::get('/anc/ajax/calc-hpl', [AncController::class, 'ajaxCalcHpl'])->name('anc.ajax.calc-hpl');
            Route::get('/anc/ajax/calc-imt', [AncController::class, 'ajaxCalcImt'])->name('anc.ajax.calc-imt');

            Route::get('/anc/{anc}/kartu', [AncController::class, 'kartu'])
                ->whereNumber('anc')->name('anc.kartu');
            Route::post('/anc/{anc}/visit', [AncController::class, 'storeVisit'])
                ->whereNumber('anc')->name('anc.visit.store');
            Route::delete('/anc/visits/{visit}', [AncController::class, 'destroyVisit'])
                ->whereNumber('visit')->name('anc.visit.destroy');

            Route::resource('anc', AncController::class)
                ->parameters(['anc' => 'anc'])
                ->whereNumber('anc');
        });

        // ===== Pelayanan INC (Persalinan) =====
        Route::middleware('permission:inc.view')->group(function () {
            Route::get('/inc/{inc}/kartu', [IncController::class, 'kartu'])
                ->whereNumber('inc')->name('inc.kartu');
            Route::match(['get', 'post'], '/inc/{inc}/surat-rujukan', [IncController::class, 'suratRujukan'])
                ->whereNumber('inc')->name('inc.surat-rujukan');
            Route::post('/inc/{inc}/set-rujuk-siklus', [IncController::class, 'setRujukSiklus'])
                ->whereNumber('inc')->name('inc.set-rujuk-siklus');
            Route::post('/inc/{inc}/soap', [IncController::class, 'storeSoap'])
                ->whereNumber('inc')->name('inc.soap.store');
            Route::delete('/inc/soap/{soap}', [IncController::class, 'destroySoap'])
                ->whereNumber('soap')->name('inc.soap.destroy');

            Route::resource('inc', IncController::class)
                ->parameters(['inc' => 'inc'])
                ->whereNumber('inc');
        });

        // ===== Pelayanan PNC (Nifas) =====
        Route::middleware('permission:pnc.view')->group(function () {
            Route::get('/pnc',              [PncController::class, 'index'])->name('pnc.index');
            Route::get('/pnc/{pnc}',        [PncController::class, 'show'])->whereNumber('pnc')->name('pnc.show');
            Route::post('/pnc/{pnc}/visit', [PncController::class, 'storeVisit'])->whereNumber('pnc')->name('pnc.visit.store');
            Route::delete('/pnc/visits/{visit}', [PncController::class, 'destroyVisit'])->whereNumber('visit')->name('pnc.visit.destroy');
        });

        // ===== Pelayanan KN (Neonatus) =====
        Route::middleware('permission:kn.view')->group(function () {
            Route::get('/kn',              [KnController::class, 'index'])->name('kn.index');
            Route::get('/kn/{kn}',         [KnController::class, 'show'])->whereNumber('kn')->name('kn.show');
            Route::get('/kn/{kn}/edit',    [KnController::class, 'edit'])->whereNumber('kn')->name('kn.edit');
            Route::put('/kn/{kn}',         [KnController::class, 'update'])->whereNumber('kn')->name('kn.update');
            Route::post('/kn/{kn}/visit',  [KnController::class, 'storeVisit'])->whereNumber('kn')->name('kn.visit.store');
            Route::delete('/kn/visits/{visit}', [KnController::class, 'destroyVisit'])->whereNumber('visit')->name('kn.visit.destroy');
        });

        // ===== Pelayanan Bayi/Anak (Imunisasi + Tumbuh Kembang) =====
        Route::middleware('permission:child.view')->group(function () {
            Route::get('/child',                  [ChildController::class, 'index'])->name('child.index');
            Route::get('/child/{child}',          [ChildController::class, 'show'])->whereNumber('child')->name('child.show');
            Route::post('/child/{child}/immunization', [ChildController::class, 'storeImmunization'])->whereNumber('child')->name('child.immunization.store');
            Route::delete('/child/immunization/{record}', [ChildController::class, 'destroyImmunization'])->whereNumber('record')->name('child.immunization.destroy');
            Route::post('/child/{child}/visit',   [ChildController::class, 'storeVisit'])->whereNumber('child')->name('child.visit.store');
            Route::delete('/child/visits/{visit}', [ChildController::class, 'destroyVisit'])->whereNumber('visit')->name('child.visit.destroy');
        });

        // ===== Rekam Medis Pasien (Integrated View) =====
        Route::middleware('permission:patients.view')->group(function () {
            Route::get('/medical-record',              [MedicalRecordController::class, 'index'])->name('rm.index');
            Route::get('/medical-record/ajax/suggest', [MedicalRecordController::class, 'ajaxSuggest'])->name('rm.ajax.suggest');
            Route::get('/medical-record/{rm}',         [MedicalRecordController::class, 'show'])->whereNumber('rm')->name('rm.show');
        });

        // ===== Master Klinik (Site Settings) =====
        Route::middleware('permission:sites.view')->group(function () {
            Route::get('/sites',              [SiteController::class, 'index'])->name('sites.index');
            Route::get('/sites/{site}/edit',  [SiteController::class, 'edit'])->whereNumber('site')->name('sites.edit');
            Route::put('/sites/{site}',       [SiteController::class, 'update'])->whereNumber('site')->name('sites.update');
            Route::delete('/sites/{site}/logo', [SiteController::class, 'destroyLogo'])->whereNumber('site')->name('sites.logo.destroy');
            Route::delete('/sites/{site}/kop',  [SiteController::class, 'destroyKop'])->whereNumber('site')->name('sites.kop.destroy');
        });
    });
});
