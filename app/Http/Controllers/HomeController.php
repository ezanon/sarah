<?php

// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;
use App\Models\Sala;
use App\Models\Veiculo;

class HomeController extends Controller
{
    public function index()
    {
        $minhaSala = Sala::with(['tipo', 'bloco', 'andar'])
            ->where('user_id', auth()->id())
            ->first();

        $veiculos = Veiculo::where('user_id', auth()->id())->latest()->get();

        return view('home', compact('minhaSala', 'veiculos'));
    }
}