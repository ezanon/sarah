<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\Veiculo;
use App\Models\LinkAcademico; 
use App\Models\OdsUsuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Uspdev\Replicado\Replicado;

class HomeController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();

        // Valores padrão para visitantes não autenticados
        $minhaSala = null;
        $veiculos = collect();
        $links = collect();
        $minhasOds = [];
        $nivelCnpq = null;
        $fotoCustomUrl = null;
        $codpes = null;

        // Lista de ODS é estática, pode carregar sempre
        $odsList = \App\Http\Controllers\OdsController::ODS_LIST;

        // Se houver usuário logado, busca os dados dele
        if ($user) {
            $codpes = $user->codpes ?? null;

            $minhaSala = \App\Models\Sala::with(['tipo', 'bloco', 'andar'])
                ->where('user_id', $user->id)->first();

            $veiculos = \App\Models\Veiculo::where('user_id', $user->id)->latest()->get();
            $links = \App\Models\LinkAcademico::where('user_id', $user->id)->get();
            $minhasOds = \App\Models\OdsUsuario::where('user_id', $user->id)->pluck('ods_id')->toArray();
            $nivelCnpq = $user->nivel_cnpq ?? null;

            // Foto customizada (com cache busting)
            if ($codpes) {
                $fotoPath = "fotos/{$codpes}.jpg";
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($fotoPath)) {
                    $timestamp = \Illuminate\Support\Facades\Storage::disk('public')->lastModified($fotoPath);
                    $fotoCustomUrl = asset("storage/{$fotoPath}?v={$timestamp}");
                }
            }
        }

        return view('home', compact(
            'minhaSala', 'veiculos', 'links', 'minhasOds', 'odsList', 
            'nivelCnpq', 'fotoCustomUrl'
        ));
    }
    
}