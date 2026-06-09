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
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $codpes = $user->codpes ?? null;

        $minhaSala = Sala::with(['tipo', 'bloco', 'andar'])->where('user_id', $user->id)->first();
        $veiculos = Veiculo::where('user_id', $user->id)->latest()->get();
        $links = LinkAcademico::where('user_id', $user->id)->get();
        $minhasOds = OdsUsuario::where('user_id', $user->id)->pluck('ods_id')->toArray();
        $odsList = \App\Http\Controllers\OdsController::ODS_LIST;
        $nivelCnpq = $user->nivel_cnpq ?? null;

        // 📷 APENAS FOTO CUSTOMIZADA saRAh (com cache busting)
        $fotoCustomUrl = null;
        $fotoPath = "fotos/{$codpes}.jpg";

        if ($codpes && Storage::disk('public')->exists($fotoPath)) {
            // Pega o timestamp da última modificação do arquivo
            $timestamp = Storage::disk('public')->lastModified($fotoPath);
            $fotoCustomUrl = asset("storage/{$fotoPath}?v={$timestamp}");
        }

        return view('home', compact(
            'minhaSala', 'veiculos', 'links', 'minhasOds', 'odsList', 
            'nivelCnpq', 'fotoCustomUrl'
        ));        
    }
}