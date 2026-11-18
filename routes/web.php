<?php

use App\Http\Controllers\Auth\BejelentkezesController;
use App\Http\Controllers\Auth\RegisztracioController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\DiagController;
use App\Http\Controllers\GyakorlatController;
use App\Http\Controllers\TervController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to(Auth::check() ? route('terv.tervem') : route('login'));
});

// Diagnosztika — csak COLLEY_DIAG=true mellett él (különben 404)
Route::get('/diag', DiagController::class)->name('diag');

/*
 * Hitelesítés. A GET /bejelentkezes útvonal NEVE a keretrendszer miatt kötelezően
 * 'login' — az Illuminate\Auth\Middleware\Authenticate middleware ezt a nevet hívja
 * (route('login')) egy be nem jelentkezett felhasználó visszairányításakor, az URI-tól
 * függetlenül. A 'guest' middleware-t szándékosan nem használjuk (lásd mutat() metódusok),
 * így a viselkedés verziófüggetlen és az itt leírtak szerint explicit.
 */
Route::get('/bejelentkezes', [BejelentkezesController::class, 'mutat'])->name('login');
Route::post('/bejelentkezes', [BejelentkezesController::class, 'bejelentkeztet'])->name('bejelentkezes.kuld');
Route::post('/kijelentkezes', [BejelentkezesController::class, 'kijelentkeztet'])->name('kijelentkezes');

Route::get('/regisztracio', [RegisztracioController::class, 'mutat'])->name('regisztracio');
Route::post('/regisztracio', [RegisztracioController::class, 'tarol'])->name('regisztracio.kuld');

Route::middleware('auth')->group(function () {
    Route::get('/tervem', [TervController::class, 'tervem'])->name('terv.tervem');
    Route::get('/uj-terv', [TervController::class, 'ujForma'])->name('terv.uj');
    Route::post('/terv/general', [TervController::class, 'general'])->name('terv.general');

    Route::get('/avatar', AvatarController::class)->name('avatar');

    Route::get('/gyakorlatok', [GyakorlatController::class, 'index'])->name('gyakorlatok');
    Route::get('/gyakorlatok/{gyakorlat}', [GyakorlatController::class, 'show'])->name('gyakorlatok.show');
});
