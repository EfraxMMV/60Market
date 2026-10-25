<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignLeadController;
use App\Http\Controllers\SlotCheckoutController;
use App\Http\Controllers\ViewerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — 60Market: Un Minuto Para Vender
|--------------------------------------------------------------------------
*/

/* 
|--------------------------------------------------------------------------
| 1. Portada de Pre-Registro 
|-------------------------------------------------------------------------- 
*/
Route::get('/', function () {
    return view('preregister');
})->name('preregister');

// Pre-registro de campaña - Guardado de lead (D02, publico/anonimo)
Route::post('/campaign/pre-register', [CampaignLeadController::class, ''])->name('campaign.pre-register');

// -------------------------------------------------------------
// 2. Visor Principal en Vivo & Redirección (D01, D02, D03, D04, D09)
// -------------------------------------------------------------
Route::get('/', [ViewerController::class, 'index'])->name('viewer.home');
Route::get('/r/{regionSlug}', [ViewerController::class, 'index'])->name('viewer.region');

// Endpoint de sincronización y estado del slot en vivo (D04)
Route::get('/api/viewer/{regionSlug}', [ViewerController::class, 'currentSlot'])->name('api.viewer.slot');

// Clic anónimo y redirección externa al canal del anunciante (D01, D09)
Route::get('/ads/{advertisementId}/click/{regionId}', [ViewerController::class, 'click'])->name('ads.click');

// Registro anónimo de impresión en vivo (D09)
Route::post('/api/ads/impression', [ViewerController::class, 'impression'])->name('api.ads.impression');


// -------------------------------------------------------------
// 3. Autenticación y Registro Empresarial (D06)
// -------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


// -------------------------------------------------------------
// 4. Panel de Administración de Anuncios y Borradores (D06, D07, D08)
// -------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AdvertisementController::class, 'index'])->name('dashboard');

    Route::get('/ads/create', [AdvertisementController::class, 'create'])->name('ads.create');
    Route::post('/ads', [AdvertisementController::class, 'store'])->name('ads.store');
    Route::post('/ads/generate-ai', [AdvertisementController::class, 'generateAi'])->name('ads.generate-ai');

    Route::get('/ads/{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('ads.edit');
    Route::put('/ads/{advertisement}', [AdvertisementController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{advertisement}', [AdvertisementController::class, 'destroy'])->name('ads.destroy');

    // -------------------------------------------------------------
    // 5. Compra y Programación de Turnos (Incremento 4: Mercado Pago Sandbox)
    // -------------------------------------------------------------
    Route::get('/ads/{advertisement}/checkout', [SlotCheckoutController::class, 'checkout'])->name('ads.checkout');
    Route::post('/ads/{advertisement}/checkout', [SlotCheckoutController::class, 'process'])->name('ads.checkout.process');
    Route::get('/ads/{advertisement}/checkout/success', [SlotCheckoutController::class, 'success'])->name('ads.checkout.success');
});
