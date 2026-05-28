<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\TipoSala;
use App\Models\Bloco;
use App\Models\Andar;
use App\Models\Sala;

class MinhaSalaController extends Controller {
    
    public function index() {
        $tipos = TipoSala::orderBy('nome')->get();
        $blocos = Bloco::orderBy('nome')->get();
        $andares = Andar::orderBy('numero')->get();
        $minhaSala = Sala::where('user_id', auth()->id())->first();

        return view('minhasala.index', compact('tipos', 'blocos', 'andares', 'minhaSala'));
    }

    public function store(Request $request) {
        $request->validate([
            'tipo_sala_id' => 'required|exists:tipos_sala,id',
            'bloco_id'     => 'required|exists:blocos,id',
            'andar_id'     => 'required|exists:andares,id',
            'numero'       => 'required|string|max:20',
            'descricao'    => 'nullable|string|max:500',
        ]);

        Sala::updateOrCreate(
            ['user_id' => auth()->id()],
            $request->only(['tipo_sala_id', 'bloco_id', 'andar_id', 'numero', 'descricao'])
        );

        return back()->with('success', 'Sala atualizada com sucesso!');
    }
}