<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use App\Models\Laboratorio;
use App\Models\Equipamento; 
use Illuminate\Http\Request;

class AdminEquipamentoController extends Controller
{
    public function index()
    {
        $centros = Centro::with('laboratorios')->orderBy('nome')->get();
        return view('equipamentos.admin.index', compact('centros'));
    }

    public function storeCentro(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'nullable|string|max:50',
        ]);

        Centro::create($request->only('nome', 'sigla'));
        return back()->with('success', 'Centro cadastrado com sucesso!');
    }

    public function destroyCentro(Centro $centro)
    {
        if ($centro->laboratorios()->count() > 0) {
            return back()->with('error', 'Não é possível excluir: o centro possui laboratórios vinculados.');
        }
        $centro->delete();
        return back()->with('success', 'Centro excluído com sucesso!');
    }

    public function storeLaboratorio(Request $request)
    {
        $request->validate([
            'centro_id' => 'required|exists:centros,id',
            'nome' => 'required|string|max:255',
            'sigla' => 'nullable|string|max:50',
        ]);

        Laboratorio::create($request->only('centro_id', 'nome', 'sigla'));
        return back()->with('success', 'Laboratório cadastrado com sucesso!');
    }

    public function destroyLaboratorio(Laboratorio $laboratorio)
    {
        if ($laboratorio->equipamentos()->count() > 0) {
            return back()->with('error', 'Não é possível excluir: o laboratório possui equipamentos vinculados.');
        }
        $laboratorio->delete();
        return back()->with('success', 'Laboratório excluído com sucesso!');
    }
    
    public function indexEquipamentos()
    {
        $equipamentos = Equipamento::with(['laboratorio.centro', 'criador'])
            ->latest()
            ->get();

        return view('equipamentos.admin.equipamentos', compact('equipamentos'));
    }
    
    public function toggleAtivo(Equipamento $equipamento)
    {
        $equipamento->update([
            'ativo' => !$equipamento->ativo
        ]);

        return back()->with('success', 'Status alterado com sucesso!');
    }
    
    public function gerarRelatorio()
    {
        try {
            \Artisan::call('relatorio:equipamentos');
            return back()->with('success', '✅ Relatório gerado com sucesso! Disponível no website!');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

}