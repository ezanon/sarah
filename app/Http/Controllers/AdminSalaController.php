<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\TipoSala;
use App\Models\Bloco;
use App\Models\Andar;

class AdminSalaController extends Controller {
    public function __construct() {
        // Proteção básica. Substitua por middleware('permission:admin') se usar Spatie
        $this->middleware('auth'); 
    }

    public function index() {
        return view('minhasala.admin.index', [
            'tipos'   => TipoSala::latest()->get(),
            'blocos'  => Bloco::latest()->get(),
            'andares' => Andar::latest()->get(),
        ]);
    }

    private function storeGeneric(Request $request, $model, $field, $routeName) {
        $request->validate(["$field" => "required|unique:{$model->getTable()},$field"]);
        $model::create([$field => $request->$field]);
        return redirect()->route($routeName)->with('success', 'Item adicionado!');
    }

    public function storeTipo(Request $request) {
        return $this->storeGeneric($request, new TipoSala(), 'nome', 'minhasala.admin.index');
    }
    public function storeBloco(Request $request) {
        return $this->storeGeneric($request, new Bloco(), 'nome', 'minhasala.admin.index');
    }
    public function storeAndar(Request $request) {
        return $this->storeGeneric($request, new Andar(), 'numero', 'minhasala.admin.index');
    }
}
