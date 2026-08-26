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
use App\Http\Controllers\EquipamentoController;
use App\Http\Controllers\AdminEquipamentoController;
use App\Http\Controllers\UserController;

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

// Rota pública (a view já trata o @auth / @else)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Fallback 404 personalizado
Route::fallback(function () {
    return view('errors.404');
});

// ═══════════════════════════════════════════════════════════════
// ROTAS PROTEGIDAS (requer autenticação)
// ═══════════════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {

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
        ->middleware('permission:admin')
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
    Route::patch('/user/toggle-foto-publica', [UserController::class, 'toggleFotoPublica'])->name('user.toggle-foto-publica');

    
    // ─────────────────────────────────────────────────────────
    // EQUIPAMENTOS
    // ─────────────────────────────────────────────────────────
    Route::prefix('/equipamentos')->name('equipamentos.')->group(function () {
        // Admin de Centros e Laboratórios
        Route::prefix('/admin')
                ->name('admin.')
                ->middleware('permission:c_pesquisa')
                ->group(function () {
            Route::get('/', [AdminEquipamentoController::class, 'index'])->name('index');
            Route::get('/equipamentos', [AdminEquipamentoController::class, 'indexEquipamentos'])->name('equipamentos');
            Route::post('/equipamentos/gerar-relatorio', [AdminEquipamentoController::class, 'gerarRelatorio'])->name('equipamentos.gerar-relatorio');
            Route::patch('/equipamentos/{equipamento}/toggle-ativo', [AdminEquipamentoController::class, 'toggleAtivo'])->name('equipamentos.toggle-ativo');
            Route::post('/centros', [AdminEquipamentoController::class, 'storeCentro'])->name('store.centro');
            Route::post('/laboratorios', [AdminEquipamentoController::class, 'storeLaboratorio'])->name('store.laboratorio');
            Route::delete('/centros/{centro}', [AdminEquipamentoController::class, 'destroyCentro'])->name('destroy.centro');
            Route::delete('/laboratorios/{laboratorio}', [AdminEquipamentoController::class, 'destroyLaboratorio'])->name('destroy.laboratorio');
        });

        // CRUD de Equipamentos
        Route::get('/', [EquipamentoController::class, 'index'])->name('index');
        Route::get('/criar', [EquipamentoController::class, 'create'])->name('create');
        Route::post('/', [EquipamentoController::class, 'store'])->name('store');

        // ⚠️ IMPORTANTE: Rotas com caminhos fixos devem vir ANTES das rotas com parâmetros
        Route::get('/buscar-patrimonio', [EquipamentoController::class, 'buscarPatrimonio'])->name('buscar.patrimonio');

        // Rotas com parâmetros dinâmicos (devem vir por último)
        Route::get('/{equipamento}', [EquipamentoController::class, 'show'])->name('show');
        Route::get('/{equipamento}/editar', [EquipamentoController::class, 'edit'])->name('edit');
        Route::put('/{equipamento}', [EquipamentoController::class, 'update'])->name('update');
        Route::delete('/{equipamento}', [EquipamentoController::class, 'destroy'])->name('destroy');
    });
    
    // Relatórios da Diretoria
    Route::middleware(['auth'])->prefix('painel-relatorios')->name('relatorios.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RelatorioController::class, 'index'])->name('index');
        Route::get('/criar', [\App\Http\Controllers\RelatorioController::class, 'create'])->name('create');
        Route::post('/gerar', [\App\Http\Controllers\RelatorioController::class, 'store'])->name('store');
    });

});