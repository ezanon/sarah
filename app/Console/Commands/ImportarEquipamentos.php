<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipamento;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ImportarEquipamentos extends Command
{
    protected $signature = 'importar:equipamentos';
    protected $description = 'Importa equipamentos e seus responsáveis a partir de um arquivo CSV';

    public function handle()
    {
        $arquivo = storage_path('app/equipamentos.csv');

        if (!file_exists($arquivo)) {
            $this->error('Arquivo CSV não encontrado em: ' . $arquivo);
            return 1;
        }

        $this->info(' Iniciando importação de equipamentos...');

        $handle = fopen($arquivo, 'r');
        fgetcsv($handle); // Pula cabeçalho

        $importados = 0;
        $atualizados = 0;
        $pulados = 0;
        $erros = 0;
        $responsaveisVinculados = 0;

        while (($dados = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if (empty($dados[2])) {
                continue;
            }

            try {
                // ✅ CORREÇÃO 1: Converter string vazia em NULL para o campo patrimonio
                $patrimonio = !empty(trim($dados[8])) ? trim($dados[8]) : null;

                // ✅ CORREÇÃO 2: Verificar se já existe equipamento com este patrimônio
                if ($patrimonio !== null) {
                    $existente = Equipamento::where('patrimonio', $patrimonio)->first();
                    if ($existente) {
                        $this->warn("⚠️ Patrimônio '{$patrimonio}' já existe ({$existente->nome}). Vinculando responsáveis ao existente.");

                        // Apenas vincula os responsáveis ao equipamento existente
                        if (!empty($dados[9])) {
                            $codpesLista = array_map('trim', explode(',', $dados[9]));
                            $userIds = [];
                            foreach ($codpesLista as $codpes) {
                                if (is_numeric($codpes) && $codpes > 0) {
                                    $user = User::where('codpes', $codpes)->first();
                                    if ($user) {
                                        $userIds[] = $user->id;
                                    }
                                }
                            }
                            if (!empty($userIds)) {
                                $existente->responsaveis()->syncWithoutDetaching($userIds);
                                $responsaveisVinculados += count($userIds);
                            }
                        }
                        $pulados++;
                        continue;
                    }
                }

                // Tratamento de valores
                $anoAquisicao = !empty($dados[5]) ? (int) $dados[5] : null;
                $laboratorioId = !empty($dados[1]) ? (int) $dados[1] : null;
                $userIdCriador = !empty($dados[0]) ? (int) $dados[0] : 1;

                // Cria o equipamento (com patrimonio = null quando vazio)
                $equipamento = Equipamento::create([
                    'user_id' => $userIdCriador,
                    'laboratorio_id' => $laboratorioId,
                    'nome' => $dados[2],
                    'marca' => !empty($dados[3]) ? $dados[3] : null,
                    'modelo' => !empty($dados[4]) ? $dados[4] : null,
                    'ano_aquisicao' => $anoAquisicao,
                    'financiamento' => !empty($dados[6]) ? $dados[6] : null,
                    'cod_processo_convenio' => !empty($dados[7]) ? $dados[7] : null,
                    'patrimonio' => $patrimonio, // ✅ NULL em vez de string vazia
                    'ativo' => true,
                ]);

                // Processa responsáveis
                if (!empty($dados[9])) {
                    $codpesLista = array_map('trim', explode(',', $dados[9]));
                    $userIdsParaVincular = [];

                    foreach ($codpesLista as $codpes) {
                        if (is_numeric($codpes) && $codpes > 0) {
                            $user = User::where('codpes', $codpes)->first();
                            if ($user) {
                                $userIdsParaVincular[] = $user->id;
                            } else {
                                $this->warn("️ Usuário com codpes {$codpes} não encontrado para: {$dados[2]}");
                            }
                        }
                    }

                    if (!empty($userIdsParaVincular)) {
                        $equipamento->responsaveis()->attach($userIdsParaVincular);
                        $responsaveisVinculados += count($userIdsParaVincular);
                    }
                }

                $importados++;
            } catch (\Exception $e) {
                $this->error("❌ Erro ao importar linha ({$dados[2]}): " . $e->getMessage());
                $erros++;
            }
        }

        fclose($handle);

        $this->info("✅ Importação concluída!");
        $this->info("   - Equipamentos criados: {$importados}");
        $this->info("   - Equipamentos existentes (apenas vínculos): {$pulados}");
        $this->info("   - Vínculos de responsáveis: {$responsaveisVinculados}");
        if ($erros > 0) {
            $this->error("   - Erros: {$erros}");
        }
    }

}