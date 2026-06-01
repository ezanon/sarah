<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CnpqController extends Controller
{
    // Níveis oficiais do CNPq (Produtividade em Pesquisa)
    public const NIVEIS = [
        '1A' => 'PQ-1A',
        '1B' => 'PQ-1B',
        '1C' => 'PQ-1C',
        '1D' => 'PQ-1D',
        '2'  => 'PQ-2',
        'DT' => 'DT (Desenvolvimento Tecnológico)',
    ];

    public function index()
    {
        $nivelAtual = Auth::user()->nivel_cnpq ?? '';
        return view('cnpq.index', compact('nivelAtual'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nivel_cnpq' => 'nullable|in:1A,1B,1C,1D,2,DT'
        ]);

        Auth::user()->update([
            'nivel_cnpq' => $request->nivel_cnpq ?: null
        ]);

        return back()->with('success', 'Nível CNPq atualizado com sucesso!');
    }
}