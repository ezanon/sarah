<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VeiculoController extends Controller {

    public function index() {
        $veiculos = Veiculo::where('user_id', Auth::id())->latest()->get();
        return view('veiculos.index', compact('veiculos'));
    }

    public function store(Request $request) {
        // 1 Normaliza a placa ANTES da validação (remove hífen e converte para maiúsculas)
        $placaNormalizada = strtoupper(str_replace('-', '', $request->input('placa')));
        $request->merge(['placa' => $placaNormalizada]);

        // 2 Validação (agora verifica unicidade e formato corretamente)
        $request->validate([
            'placa' => ['required', 'string', 'max:10', 'unique:veiculos,placa', 'regex:/^[A-Z]{3}([0-9]{4}|[0-9][A-Z][0-9]{2})$/'],
            'tipo' => 'required|in:carro,moto',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'cor' => 'required|string|max:50',
        ]);

        // 3 Cria o registro
        Veiculo::create([
            'user_id' => Auth::id(),
            'placa' => $placaNormalizada,
            'tipo' => $request->tipo,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'cor' => $request->cor,
        ]);

        return back()->with('success', 'Veículo cadastrado com sucesso!');
    }

    public function destroy(Veiculo $veiculo) {
        // Segurança: só permite excluir se for do próprio usuário
        if ($veiculo->user_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        $veiculo->delete();
        return back()->with('success', 'Veículo removido.');
    }
}
