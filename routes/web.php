<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KambingController;
use App\Http\Controllers\KelahiranController;
use App\Http\Controllers\KesiapanKawinController;
use App\Http\Controllers\PengingatPerawatanController;
use App\Http\Controllers\PerkawinanController;
use App\Http\Controllers\RiwayatSakitController;
use App\Http\Controllers\SemprotKandangController;
use App\Http\Controllers\SilsilahController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/kesiapan-kawin', [KesiapanKawinController::class, 'index'])->name('kesiapan-kawin.index');

Route::get('/semprot-kandang', [SemprotKandangController::class, 'index'])->name('semprot-kandang.index');
Route::post('/semprot-kandang', [SemprotKandangController::class, 'store'])->name('semprot-kandang.store');

Route::resource('kambings', KambingController::class);
Route::resource('perkawinans', PerkawinanController::class);
Route::resource('kelahirans', KelahiranController::class);

Route::resource('riwayat-sakit', RiwayatSakitController::class)->except(['show', 'edit', 'update']);
Route::post('riwayat-sakit/{riwayatSakit}/konfirmasi', [RiwayatSakitController::class, 'konfirmasi'])->name('riwayat-sakit.konfirmasi');

Route::resource('pengingat-perawatan', PengingatPerawatanController::class)->except(['show', 'edit', 'update']);
Route::post('pengingat-perawatan/{pengingatPerawatan}/selesai', [PengingatPerawatanController::class, 'selesai'])->name('pengingat-perawatan.selesai');

Route::get('silsilah/{kambing?}', [SilsilahController::class, 'index'])->name('silsilah.index');
Route::get('silsilah/{kambing}/data', [SilsilahController::class, 'data'])->name('silsilah.data');


