<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\Veiculo;
use App\Models\LinkAcademico; 
use App\Models\OdsUsuario;
use App\Http\Controllers\OdsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Uspdev\Replicado\Replicado;
use Uspdev\Wsfoto; // ✅ Adicione este import

class HomeController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();

        // Se não estiver logado, retorna apenas a view de login
        if (!$user) {
            return view('home', [
                'veiculos' => collect(),
                'fotoCustomUrl' => null,
                'fotoUspUrl' => null, // ✅ Adicione
                'minhaSala' => null,
                'links' => collect(),
                'nivelCnpq' => null,
                'minhasOds' => [],
                'odsList' => OdsController::ODS_LIST,
                'equipamentos' => collect(),
            ]);
        }

        $codpes = $user->codpes ?? null;

        // Veículos
        $veiculos = \App\Models\Veiculo::where('user_id', $user->id)->latest()->get();

        // Foto USP (oficial)
        $fotoUspUrl = null;
        if ($codpes) {
            try {
                $fotoBase64 = Wsfoto::obter($codpes);
                if ($fotoBase64) {
                    $fotoUspUrl = 'data:image/png;base64,' . $fotoBase64;
                }
            } catch (\Exception $e) {
                // Se falhar, deixa null
                $fotoUspUrl = null;
            }
        }

        // Foto customizada (com cache busting)
        $fotoCustomUrl = null;
        if ($codpes) {
            $fotoPath = "fotos/{$codpes}.jpg";
            if (Storage::disk('public')->exists($fotoPath)) {
                $timestamp = Storage::disk('public')->lastModified($fotoPath);
                $fotoCustomUrl = asset("storage/{$fotoPath}?v={$timestamp}");
            }
        }

        // Minha Sala
        $minhaSala = Sala::with(['tipo', 'bloco', 'andar'])
            ->where('user_id', $user->id)->first();

        // Links Acadêmicos
        $links = \App\Models\LinkAcademico::where('user_id', $user->id)->get();

        // Nível CNPq
        $nivelCnpq = $user->nivel_cnpq ?? null;

        // ODS
        $minhasOds = \App\Models\OdsUsuario::where('user_id', $user->id)->pluck('ods_id')->toArray();
        $odsList = OdsController::ODS_LIST;

        // Equipamentos (apenas os que o usuário é responsável)
        $equipamentos = \App\Models\Equipamento::with(['laboratorio.centro'])
            ->whereHas('responsaveis', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->take(5)
            ->get();

        return view('home', compact(
            'user',
            'veiculos',
            'fotoCustomUrl',
            'fotoUspUrl', // ✅ Adicione
            'minhaSala',
            'links',
            'nivelCnpq',
            'minhasOds',
            'odsList',
            'equipamentos'
        ));
    }
    
}