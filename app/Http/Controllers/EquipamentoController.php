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
        $user = auth()->user();

        $equipamentos = Equipamento::with(['laboratorio.centro', 'criador', 'responsaveis'])
            ->whereHas('responsaveis', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
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

        $data['ativo'] = $request->input('ativo') === '1' || $request->input('ativo') === 1;

        if (!$data['ativo']) {
            $data['motivo_inativacao'] = $request->input('motivo_inativacao');
        } else {
            $data['motivo_inativacao'] = null;
        }

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

        if ($request->hasFile('foto')) {
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

        $data['ativo'] = $request->input('ativo') === '1' || $request->input('ativo') === 1;

        if (!$data['ativo']) {
            $data['motivo_inativacao'] = $request->input('motivo_inativacao');
        } else {
            $data['motivo_inativacao'] = null;
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

        if ($equipamento->foto) {
            Storage::disk('public')->delete($equipamento->foto);
        }

        $equipamento->delete();

        return redirect()->route('equipamentos.index')->with('success', 'Equipamento excluído com sucesso!');
    }

    public function buscarPatrimonio(Request $request)
    {
        $patrimonio = $request->query('patrimonio');

        if (!$patrimonio) {
            return response()->json(['error' => 'Patrimônio não informado'], 400);
        }

        $patrimonio = preg_replace('/\D/', '', $patrimonio);

        if (empty($patrimonio)) {
            return response()->json(['error' => 'Patrimônio inválido'], 400);
        }

        try {
            $fields = ['anoorc', 'coddocmovvba', 'epfmarpat', 'modpat', 'vlravlbem', 'vlrbem', 'dsccplbem'];
            $data = Bempatrimoniado::dump($patrimonio, $fields);

            $bem = is_array($data) ? ($data[0] ?? $data) : $data;

            return response()->json([
                'ano_incorporacao' => $bem['anoorc'] ?? null,
                'cod_processo_incorporacao' => $bem['coddocmovvba'] ?? null,
                'marca' => $bem['epfmarpat'] ?? null,
                'modelo' => $bem['modpat'] ?? null,
                'cod_processo_convenio' => $bem['dsccplbem'] ?? null,
                'valor' => isset($bem['vlravlbem']) ? (float) $bem['vlravlbem'] : (isset($bem['vlrbem']) ? (float) $bem['vlrbem'] : null),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar no Replicado: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function validateEquipamento(Request $request, $ignoreId = null)
    {
        $rules = [
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:grande_porte,periferico',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'ano_aquisicao' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'ano_incorporacao' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'financiamento' => 'nullable|string|max:255',
            'cod_processo_convenio' => 'nullable|string|max:255',
            'patrimonio' => 'nullable|string|max:50|unique:equipamentos,patrimonio,' . $ignoreId,
            'valor' => 'nullable|numeric|min:0',
            'moeda' => 'nullable|in:BRL,USD,EUR',
            'cod_processo_incorporacao' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'ativo' => 'required|in:0,1',
            'motivo_inativacao' => 'required_if:ativo,0|nullable|in:obsoleto,danificado,doado',
        ];

        return $request->validate($rules);
    }

    private function sincronizarResponsaveis(Equipamento $equipamento, $codpesString)
    {
        $userIds = [];

        if (!empty($codpesString)) {
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