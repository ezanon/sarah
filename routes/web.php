<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MinhaSalaController;
use App\Http\Controllers\AdminSalaController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\LinkAcademicoController;
use App\Http\Controllers\OdsController;
use App\Http\Controllers\CnpqController;
use App\Http\Controllers\FotoController;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistema saRAh
|--------------------------------------------------------------------------
| Todas as rotas protegidas por autenticação (Senha Única USP)
|
*/

// ═══════════════════════════════════════════════════════════════
// ROTAS PÚBLICAS (sem autenticação)
// ═══════════════════════════════════════════════════════════════

// Fallback 404 personalizado
Route::fallback(function () {
    return view('errors.404');
});

// ═══════════════════════════════════════════════════════════════
// ROTAS PROTEGIDAS (requer autenticação)
// ═══════════════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {
    
    // ─────────────────────────────────────────────────────────
    // HOME
    // ─────────────────────────────────────────────────────────
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // ─────────────────────────────────────────────────────────
    // MINHA SALA (cadastro do usuário)
    // ─────────────────────────────────────────────────────────
    Route::controller(MinhaSalaController::class)->group(function () {
        Route::get('/minhasala', 'index')->name('minhasala.index');
        Route::post('/minhasala', 'store')->name('minhasala.store');
    });

    // ─────────────────────────────────────────────────────────
    // ADMIN SALA (gestão de tipos, blocos, andares)
    // ─────────────────────────────────────────────────────────
    Route::prefix('/minhasala/admin')
        ->name('minhasala.admin.')
        ->controller(AdminSalaController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/tipos', 'storeTipo')->name('store.tipo');
            Route::post('/blocos', 'storeBloco')->name('store.bloco');
            Route::post('/andares', 'storeAndar')->name('store.andar');
        });

    // ─────────────────────────────────────────────────────────
    // VEÍCULOS
    // ─────────────────────────────────────────────────────────
    Route::controller(VeiculoController::class)->group(function () {
        Route::get('/meusveiculos', 'index')->name('veiculos.index');
        Route::post('/meusveiculos', 'store')->name('veiculos.store');
        Route::delete('/meusveiculos/{veiculo}', 'destroy')->name('veiculos.destroy');
    });

    // ─────────────────────────────────────────────────────────
    // LINKS ACADÊMICOS
    // ─────────────────────────────────────────────────────────
    Route::controller(LinkAcademicoController::class)->group(function () {
        Route::get('/links-academicos', 'index')->name('links-academicos.index');
        Route::post('/links-academicos', 'store')->name('links-academicos.store');
        Route::delete('/links-academicos/{linkAcademico}', 'destroy')->name('links-academicos.destroy');
    });

    // ─────────────────────────────────────────────────────────
    // ODS (Objetivos de Desenvolvimento Sustentável)
    // ─────────────────────────────────────────────────────────
    Route::controller(OdsController::class)->group(function () {
        Route::get('/minhas-ods', 'index')->name('ods.index');
        Route::post('/minhas-ods', 'store')->name('ods.store');
    });

    // ─────────────────────────────────────────────────────────
    // NÍVEL CNPq
    // ─────────────────────────────────────────────────────────
    Route::controller(CnpqController::class)->group(function () {
        Route::get('/meu-cnpq', 'index')->name('cnpq.index');
        Route::post('/meu-cnpq', 'store')->name('cnpq.store');
    });

    // ─────────────────────────────────────────────────────────
    // FOTO DE PERFIL
    // ─────────────────────────────────────────────────────────
    Route::controller(FotoController::class)->group(function () {
        Route::get('/minha-foto', 'index')->name('foto.index');
        Route::post('/minha-foto', 'store')->name('foto.store');
    });

});