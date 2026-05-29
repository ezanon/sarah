<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MinhaSalaController;
use App\Http\Controllers\AdminSalaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VeiculoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Permite usar Gate::check('user')na view 404
Route::fallback(function(){
    return view('errors.404');
 });
 
 
 Route::middleware('auth')->group(function () {
     
    Route::get('/minhasala', [MinhaSalaController::class, 'index'])->name('minhasala.index');
    Route::post('/minhasala', [MinhaSalaController::class, 'store'])->name('minhasala.store');

    Route::prefix('/minhasala/admin')->name('minhasala.admin.')->group(function () {
        Route::get('/', [AdminSalaController::class, 'index'])->name('index');
        Route::post('/tipos', [AdminSalaController::class, 'storeTipo'])->name('store.tipo');
        Route::post('/blocos', [AdminSalaController::class, 'storeBloco'])->name('store.bloco');
        Route::post('/andares', [AdminSalaController::class, 'storeAndar'])->name('store.andar');
    });
    
    Route::get('/meusveiculos', [VeiculoController::class, 'index'])->name('veiculos.index');
    Route::post('/meusveiculos', [VeiculoController::class, 'store'])->name('veiculos.store');
    Route::delete('/meusveiculos/{veiculo}', [VeiculoController::class, 'destroy'])->name('veiculos.destroy');
    
});



