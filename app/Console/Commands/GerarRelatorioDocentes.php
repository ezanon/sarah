<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Uspdev\Replicado\Pessoa;

class GerarRelatorioDocentes extends Command
{
    protected $signature = 'relatorio:docentes';
    protected $description = 'Gera relatório público HTML dos docentes do IGC';

    public function handle()
    {
        $this->info('🔍 Buscando docentes...');

        $docentes = $this->obterDocentes();
        
        $this->info('📊 Processando ' . count($docentes) . ' docentes...');

        // Ordena por nome
        usort($docentes, fn($a, $b) => strcoll($a['nome'], $b['nome']));

        // Gera o HTML
        $html = View::make('relatorios.docentes', [
            'docentes' => $docentes,
            'total' => count($docentes),
        ])->render();

        // Salva o arquivo
        $path = public_path('relatorios/docentes.html');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $html);

        $this->info("✅ Relatório gerado com sucesso: {$path}");
    }

    private function obterDocentes(): array
    {
        $docentes = [];
        $departamentos = config('departamentos.codigos');

        // 1. Docentes ativos do Replicado
        try {
            $ativos = Pessoa::listarDocentes();
            foreach ($ativos as $d) {
                $docentes[] = $this->processarDocenteReplicado($d, $departamentos);
            }
            $this->info("  ✓ " . count($ativos) . " docentes ativos");
        } catch (\Exception $e) {
            $this->error("  ✗ Erro ao buscar ativos: " . $e->getMessage());
        }

        // 2. Docentes seniors (aposentados)
        try {
            $seniors = Pessoa::listarDocentesAposentadosSenior();
            foreach ($seniors as $d) {
                $docentes[] = $this->processarDocenteReplicado($d, $departamentos);
            }
            $this->info("  ✓ " . count($seniors) . " docentes seniors");
        } catch (\Exception $e) {
            $this->error("  ✗ Erro ao buscar seniors: " . $e->getMessage());
        }

        // 3. Docentes com duplo vínculo (usuários locais com duplo_vinculo preenchido)
        $duploVinculo = User::whereNotNull('duplo_vinculo')
            ->where('duplo_vinculo', '!=', '')
            ->with(['linksAcademicos'])
            ->get();
        
        foreach ($duploVinculo as $user) {
            $docentes[] = $this->processarDocenteLocal($user, $departamentos);
        }
        $this->info("  ✓ " . $duploVinculo->count() . " docentes com duplo vínculo");

        // Remove duplicatas (mesmo codpes)
        $docentes = collect($docentes)->unique('codpes')->values()->toArray();

        return $docentes;
    }

    private function processarDocenteReplicado(array $d, array $departamentos): array
    {
        $codpes = $d['codpes'];
        $codDepto = $d['nomabvset'] ?? null;
        
        // Busca dados locais do usuário
        $user = User::where('codpes', $codpes)
            ->with(['linksAcademicos'])
            ->first();
        
        $dadosLocais = $user ? $user->obterDadosPublicos() : [];
        
        return [
            'codpes' => $codpes,
            'nome' => $d['nompes'] ?? Pessoa::nomeCompleto($codpes),
            'email' => $d['codema'] ?? Pessoa::email($codpes),
            'departamento_sigla' => $this->getSiglaDepartamento($codDepto, $departamentos),
            'departamento_nome' => $this->getNomeDepartamento($codDepto, $departamentos),
            'foto_url' => $dadosLocais['foto_url'] ?? null,
            'links' => $dadosLocais['links'] ?? [],
            'ods' => $dadosLocais['ods'] ?? [],
        ];
    }

    private function processarDocenteLocal(User $user, array $departamentos): array
    {
        $codpes = $user->codpes;
        $codDepto = $user->nomabvset;
        $dadosLocais = $user->obterDadosPublicos();
        
        return [
            'codpes' => $codpes,
            'nome' => $user->name ?? Pessoa::nomeCompleto($codpes),
            'email' => $user->email ?? Pessoa::email($codpes),
            'departamento_sigla' => $this->getSiglaDepartamento($codDepto, $departamentos),
            'departamento_nome' => $this->getNomeDepartamento($codDepto, $departamentos),
            'foto_url' => $dadosLocais['foto_url'] ?? null,
            'links' => $dadosLocais['links'] ?? [],
            'ods' => $dadosLocais['ods'] ?? [],
        ];
    }

    private function getSiglaDepartamento(?string $codigo, array $departamentos): string
    {
        if ($codigo && isset($departamentos[$codigo])) {
            return $departamentos[$codigo]['sigla'];
        }
        return $codigo ?? 'N/A';
    }

    private function getNomeDepartamento(?string $codigo, array $departamentos): string
    {
        if ($codigo && isset($departamentos[$codigo])) {
            return $departamentos[$codigo]['nome'];
        }
        return $codigo ?? 'Não informado';
    }
}