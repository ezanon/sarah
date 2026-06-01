<?php

// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;
use App\Models\Sala;
use App\Models\Veiculo;
use App\Models\OdsUsuario;

class HomeController extends Controller
{
    public function index()
    {
        $minhaSala = Sala::with(['tipo', 'bloco', 'andar'])
            ->where('user_id', auth()->id())
            ->first();

        $veiculos = Veiculo::where('user_id', auth()->id())->latest()->get();

        $links = \App\Models\LinkAcademico::where('user_id', auth()->id())->get();
        
        $minhasOds = OdsUsuario::where('user_id', auth()->id())->pluck('ods_id')->toArray();
        $odsList = \App\Http\Controllers\OdsController::ODS_LIST;

        $nivelCnpq = auth()->user()->nivel_cnpq ?? null;
        
        return view('home', compact('minhaSala', 'veiculos', 'links', 'minhasOds', 'odsList', 'nivelCnpq'));
        
    }
}