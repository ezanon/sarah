<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\Laboratorio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Uspdev\Replicado\Bempatrimoniado;

class EquipamentoController extends Controller
{
    public function index()
    {
        // Lista todos os equipamentos, carregando laboratório e centro
        $equipamentos = Equipamento::with(['laboratorio.centro', 'criador', 'responsaveis'])
            ->latest()
            ->get();

        return view('equipamentos.index', compact('equipamentos'));
    }

    public function create()
    {
        $laboratorios = Laboratorio::select('laboratorios.*')
            ->join('centros', 'laboratorios.centro_id', '=', 'centros.id')
            ->orderBy('centros.sigla')
            ->orderBy('laboratorios.sigla')
            ->get();
        return view('equipamentos.create', compact('laboratorios'));
    }

    public function store(Request $request)
    {
        $data = $this->validateEquipamento($request);
        $data['user_id'] = auth()->id();

        // Upload da foto
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('fotosEquipamentos', 'public');
        }

        $equipamento = Equipamento::create($data);
        $this->sincronizarResponsaveis($equipamento, $request->input('responsaveis_codpes'));

        return redirect()->route('equipamentos.show', $equipamento)->with('success', 'Equipamento cadastrado com sucesso!');
    }

    public function show(Equipamento $equipamento)
    {
        $equipamento->load(['laboratorio.centro', 'criador', 'responsaveis']);
        return view('equipamentos.show', compact('equipamento'));
    }

    public function edit(Equipamento $equipamento)
    {
        // Verifica permissão
        if (!$equipamento->podeEditar(auth()->user())) {
            abort(403, 'Você não tem permissão para editar este equipamento.');
        }

        $laboratorios = Laboratorio::select('laboratorios.*')
            ->join('centros', 'laboratorios.centro_id', '=', 'centros.id')
            ->orderBy('centros.sigla')
            ->orderBy('laboratorios.sigla')
            ->get();
        return view('equipamentos.edit', compact('equipamento', 'laboratorios'));
    }

    public function update(Request $request, Equipamento $equipamento)
    {
        if (!$equipamento->podeEditar(auth()->user())) {
            abort(403, 'Você não tem permissão para editar este equipamento.');
        }

        $data = $this->validateEquipamento($request, $equipamento->id);

        // Gerenciamento da foto
        if ($request->hasFile('foto')) {
            // Apaga a foto antiga se existir
            if ($equipamento->foto) {
                Storage::disk('public')->delete($equipamento->foto);
            }
            $data['foto'] = $request->file('foto')->store('fotosEquipamentos', 'public');
        } elseif ($request->has('remover_foto')) {
            if ($equipamento->foto) {
                Storage::disk('public')->delete($equipamento->foto);
            }
            $data['foto'] = null;
        }

        $equipamento->update($data);
        $this->sincronizarResponsaveis($equipamento, $request->input('responsaveis_codpes'));

        return redirect()->route('equipamentos.show', $equipamento)->with('success', 'Equipamento atualizado com sucesso!');
    }

    public function destroy(Equipamento $equipamento)
    {
        if (!$equipamento->podeEditar(auth()->user())) {
            abort(403, 'Você não tem permissão para excluir este equipamento.');
        }

        // Apaga a foto do storage
        if ($equipamento->foto) {
            Storage::disk('public')->delete($equipamento->foto);
        }

        $equipamento->delete();

        return redirect()->route('equipamentos.index')->with('success', 'Equipamento excluído com sucesso!');
    }

    /**
     * Endpoint AJAX para buscar dados do Replicado via Patrimônio
     */
    public function buscarPatrimonio(Request $request)
    {
        $patrimonio = $request->query('patrimonio');
        
        if (!$patrimonio) {
            return response()->json(['error' => 'Patrimônio não informado'], 400);
        }

        try {
            $fields = ['anoorc', 'coddocmovvba', 'epfmarpat', 'modpat', 'vlravlbem'];
            $data = Bempatrimoniado::dump($patrimonio, $fields);

            if (empty($data)) {
                return response()->json(['error' => 'Patrimônio não encontrado no Replicado'], 404);
            }

            // O dump retorna um array associativo ou lista
            $bem = $data[0] ?? $data;

            return response()->json([
                'ano_incorporacao' => $bem['anoorc'] ?? null,
                'cod_processo_incorporacao' => $bem['coddocmovvba'] ?? null,
                'marca' => $bem['epfmarpat'] ?? null,
                'modelo' => $bem['modpat'] ?? null,
                'valor' => $bem['vlravlbem'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar no Replicado: ' . $e->getMessage()], 500);
        }
    }

    // --- Métodos Privados Auxiliares ---

    private function validateEquipamento(Request $request, $ignoreId = null)
    {
        $rules = [
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'nome' => 'required|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'ano_aquisicao' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'ano_incorporacao' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'financiamento' => 'nullable|string|max:255',
            'cod_processo_convenio' => 'nullable|string|max:255',
            'patrimonio' => 'nullable|string|max:50|unique:equipamentos,patrimonio,' . $ignoreId,
            'valor' => 'nullable|numeric|min:0',
            'cod_processo_incorporacao' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];

        return $request->validate($rules);
    }

    private function sincronizarResponsaveis(Equipamento $equipamento, $codpesString)
    {
        $userIds = [];
        if (!empty($codpesString)) {
            // Aceita codpes separados por vírgula ou quebra de linha
            $codpesArray = preg_split('/[\s,]+/', $codpesString);
            
            foreach ($codpesArray as $codpes) {
                $codpes = trim($codpes);
                if (empty($codpes)) continue;
                
                $user = User::where('codpes', $codpes)->first();
                if ($user) {
                    $userIds[] = $user->id;
                }
            }
        }

        $equipamento->responsaveis()->sync($userIds);
    }
}