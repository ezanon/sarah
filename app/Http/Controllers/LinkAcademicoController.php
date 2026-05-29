<?php

namespace App\Http\Controllers;

use App\Models\LinkAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkAcademicoController extends Controller
{
    public function index()
    {
        $links = LinkAcademico::where('user_id', Auth::id())->get();
        $plataformas = LinkAcademico::getPlataformas();
        
        return view('links-academicos.index', compact('links', 'plataformas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plataforma' => 'required|in:' . implode(',', array_keys(LinkAcademico::getPlataformas())),
            'identificador' => 'required|string|max:255',
        ]);

        // Atualiza ou cria (um link por plataforma)
        LinkAcademico::updateOrCreate(
            ['user_id' => Auth::id(), 'plataforma' => $request->plataforma],
            ['identificador' => trim($request->identificador)]
        );

        return back()->with('success', 'Link atualizado com sucesso!');
    }

    public function destroy(LinkAcademico $linkAcademico)
    {
        if ($linkAcademico->user_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }
        
        $linkAcademico->delete();
        return back()->with('success', 'Link removido.');
    }
}