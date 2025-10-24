<?php

use App\Http\Controllers\SocieteController;
use App\Http\Controllers\AyantsendroitsController; // ← AJOUTER ICI
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Connexions/login');
})->name('home');

Route::get('/inscrire', function () {
    return Inertia::render('Connexions/register');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource("societe",SocieteController::class);
// Route::get('/societe', [SocieteController::class, 'index'])->name('societe.index');
// Route::get('/societe/suivi', [SocieteController::class, 'suivi'])->name('societe.suivi');
// Route::get('/societe/histori', [SocieteController::class, 'histori'])->name('societe.histori');
// Route::get('/societe/Paiement', [SocieteController::class, 'Paiement'])->name('societe.Paiement');
// Route::get('/societe/alt', [SocieteController::class, 'alt'])->name('societe.alt');

Route::get('/AyantsEndroit/LAD', [AyantsendroitsController::class, 'LAD'])->name('AyantsEndroit.LAD');
Route::get('/AyantsEndroit/Badges', [AyantsendroitsController::class, 'Badges'])->name('AyantsEndroit.Badges');
Route::get('/AyantsEndroit/Qrcode', [AyantsendroitsController::class, 'Qrcode'])->name('AyantsEndroit.Qrcode');
Route::get('/AyantsEndroit/SuiviA', [AyantsendroitsController::class, 'SuiviA'])->name('AyantsEndroit.SuiviA');
Route::get('/AyantsEndroit/Infos', [AyantsendroitsController::class, 'Infos'])->name('AyantsEndroit.Infos');
Route::get('/AyantsEndroit/Benefices', [AyantsendroitsController::class, 'Benefices'])->name('AyantsEndroit.Benefices');
Route::get('/AyantsEndroit/Historie', [AyantsendroitsController::class, 'Historie'])->name('AyantsEndroit.Historie');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
