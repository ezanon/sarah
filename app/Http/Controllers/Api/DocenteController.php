<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Uspdev\Replicado\Lattes;
use Uspdev\Replicado\Pessoa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DocenteController extends Controller
{
    public function show(string $codpes)
    {
        $cacheKey = "docente_api_{$codpes}";
        
        return Cache::remember($cacheKey, 86400, function () use ($codpes) {
            $user = User::where('codpes', $codpes)->first();
            
            if (!$user) {
                return response()->json(['error' => 'Docente não encontrado'], 404);
            }

            // Busca departamento EXATAMENTE como o relatorio:docentes faz
            $deptoInfo = $this->obterDepartamento($codpes, $user->nomabvset);
            $ods = $this->obterODS($user->id);

            // Verifica se é docente senior
            $ehSenior = $this->verificarSeESenior($codpes);

            $dados = [
                'codpes' => $user->codpes,
                'nome' => $user->name,
                'email' => $user->email,
                'foto_url' => $user->obterDadosPublicos()['foto_url'],
                'departamento' => $deptoInfo,
                'nivel_cnpq' => $user->nivel_cnpq,
                'duplo_vinculo' => $user->duplo_vinculo,
                'eh_senior' => $ehSenior, // ✅ Novo campo
                'links' => $user->obterDadosPublicos()['links'],
                'ods' => $ods,
                'lattes' => $this->buscarDadosLattes($codpes, $user->name),
            ];

            return $dados;
        });
    }
    
    /**
    * Verifica se o docente é Senior (aposentado em atividade)
    */
   private function verificarSeESenior(string $codpes): bool
   {
       try {
           $seniors = Pessoa::listarDocentesAposentadosSenior();
           foreach ($seniors as $senior) {
               if ($senior['codpes'] == $codpes) {
                   return true;
               }
           }
       } catch (\Exception $e) {
           \Log::warning("Erro ao verificar senior para {$codpes}: " . $e->getMessage());
       }
       return false;
   }

    private function obterDepartamento(string $codpes, ?string $nomabvset): array
    {
        $departamentos = config('departamentos.codigos');
        
        // 1. Se tiver no banco (duplo vínculo ou importado via CSV)
        if (!empty($nomabvset)) {
            if (isset($departamentos[$nomabvset])) {
                return $departamentos[$nomabvset];
            }
            return ['sigla' => $nomabvset, 'nome' => $nomabvset];
        }
        
        // 2. Busca no Replicado
        try {
            // Busca como docente ativo
            $docentesAtivos = Pessoa::listarDocentes();
            foreach ($docentesAtivos as $docente) {
                if ($docente['codpes'] == $codpes) {
                    $codDepto = $docente['nomabvset'] ?? null;
                    if ($codDepto && isset($departamentos[$codDepto])) {
                        return $departamentos[$codDepto];
                    }
                    // Se tem código mas não está na config, tenta buscar a sigla
                    if ($codDepto) {
                        // Tenta achar a sigla percorrendo a config
                        foreach ($departamentos as $info) {
                            if ($info['sigla'] === $codDepto) {
                                return $info;
                            }
                        }
                        return ['sigla' => $codDepto, 'nome' => $codDepto];
                    }
                }
            }
            
            // Tenta como docente senior
            $docentesSenior = Pessoa::listarDocentesAposentadosSenior();
            foreach ($docentesSenior as $docente) {
                if ($docente['codpes'] == $codpes) {
                    $codDepto = $docente['nomabvset'] ?? null;
                    if ($codDepto && isset($departamentos[$codDepto])) {
                        return $departamentos[$codDepto];
                    }
                    if ($codDepto) {
                        foreach ($departamentos as $info) {
                            if ($info['sigla'] === $codDepto) {
                                return $info;
                            }
                        }
                        return ['sigla' => $codDepto, 'nome' => $codDepto];
                    }
                }
            }
            
        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar depto no Replicado para {$codpes}: " . $e->getMessage());
        }
        
        return ['sigla' => 'N/A', 'nome' => 'Não informado'];
    }

    private function obterODS(int $userId): array
    {
        $odsIds = DB::table('ods_usuario')
            ->where('user_id', $userId)
            ->pluck('ods_id')
            ->toArray();
        
        if (empty($odsIds)) {
            return [];
        }
        
        $odsList = \App\Http\Controllers\OdsController::ODS_LIST;
        $ods = [];
        
        foreach ($odsIds as $id) {
            $ods[] = [
                'id' => $id,
                'nome' => $odsList[$id]['nome'] ?? "ODS {$id}",
                'img' => asset("images/ods/SDG-" . str_pad($id, 2, '0', STR_PAD_LEFT) . ".jpg"),
                'url' => "https://brasil.un.org/pt-br/sdgs/{$id}",
            ];
        }
        
        return $ods;
    }

    private function buscarDadosLattes(string $codpes, string $nomeCompleto): array
    {
        try {
            $arrayLattes = Lattes::obterArray($codpes);

            if (empty($arrayLattes)) {
                return ['erro' => 'Currículo Lattes não encontrado ou indisponível.'];
            }

            $nomeCitacoes = $arrayLattes['DADOS-GERAIS']['@attributes']['NOME-EM-CITACOES-BIBLIOGRAFICAS'] ?? $nomeCompleto;

            // Linhas de pesquisa
            $linhasPesquisa = [];
            try {
                $linhasRetornadas = Lattes::listarLinhasPesquisa($codpes, $arrayLattes);
                if ($linhasRetornadas && is_array($linhasRetornadas)) {
                    $linhasPesquisa = $linhasRetornadas;
                }
            } catch (\Exception $e) {
                \Log::warning("Erro ao buscar linhas de pesquisa para {$codpes}: " . $e->getMessage());
            }

            $resumo = Lattes::retornarResumoCV($codpes, 'pt', $arrayLattes) ?? '';
            $dataAtualizacao = Lattes::retornarDataUltimaAtualizacao($codpes);

            // Formação acadêmica
            $formacao = [];
            try {
                $formacaoRaw = Lattes::retornarFormacaoAcademica($codpes, $arrayLattes);
                if ($formacaoRaw && is_array($formacaoRaw)) {
                    $tiposTraducao = [
                        'GRADUACAO' => 'Graduação',
                        'MESTRADO' => 'Mestrado',
                        'DOUTORADO' => 'Doutorado',
                        'POS-DOUTORADO' => 'Pós-Doutorado',
                        'LIVRE-DOCENCIA' => 'Livre Docência',
                        'ESPECIALIZACAO' => 'Especialização',
                    ];

                    foreach ($formacaoRaw as $tipo => $itens) {
                        $nivelTraduzido = $tiposTraducao[$tipo] ?? $tipo;

                        if (is_array($itens)) {
                            foreach ($itens as $item) {
                                $formacao[] = [
                                    'nivel' => $nivelTraduzido,
                                    'curso' => $item['NOME-CURSO'] ?? $item['NOME-COMPLETO-DO-CURSO'] ?? '',
                                    'instituicao' => $item['NOME-INSTITUICAO'] ?? '',
                                    'ano' => $item['ANO-DE-CONCLUSAO'] ?? '',
                                    'titulo_trabalho' => $item['TITULO-DA-DISSERTACAO-TESE'] 
                                        ?? $item['TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO'] 
                                        ?? $item['TITULO-DO-TRABALHO'] 
                                        ?? $item['TITULO-DA-MONOGRAFIA'] 
                                        ?? '',
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Erro ao buscar formação acadêmica para {$codpes}: " . $e->getMessage());
            }

            // Últimos 10 artigos
            $artigos = [];
            try {
                $artigosRaw = Lattes::listarArtigos($codpes, $arrayLattes, 'registros', 10);
                if ($artigosRaw && is_array($artigosRaw)) {
                    foreach ($artigosRaw as $artigo) {
                        $titulo = $artigo['TITULO-DO-ARTIGO'] ?? '';

                        $autoresArr = $artigo['AUTORES'] ?? [];
                        $autoresDestacados = '';
                        if (is_array($autoresArr) && !empty($autoresArr)) {
                            $partes = [];
                            foreach ($autoresArr as $autor) {
                                $nomeAutor = $autor['NOME-PARA-CITACAO'] ?? '';
                                $nomeNorm = $this->normalizar($nomeCitacoes);
                                $autorNorm = $this->normalizar($nomeAutor);

                                if (str_contains($autorNorm, $nomeNorm) || str_contains($nomeNorm, $autorNorm)) {
                                    $partes[] = "<strong>{$nomeAutor}</strong>";
                                } else {
                                    $partes[] = $nomeAutor;
                                }
                            }
                            $autoresDestacados = implode('; ', $partes);
                        }

                        $artigos[] = [
                            'titulo' => $titulo,
                            'autores' => $autoresDestacados,
                            'ano' => $artigo['ANO'] ?? '',
                            'revista' => $artigo['TITULO-DO-PERIODICO-OU-REVISTA'] ?? '',
                            'volume' => $artigo['VOLUME'] ?? '',
                            'pagina_inicial' => $artigo['PAGINA-INICIAL'] ?? '',
                            'pagina_final' => $artigo['PAGINA-FINAL'] ?? '',
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Erro ao buscar artigos para {$codpes}: " . $e->getMessage());
            }

            // ✅ RETORNO DOS DADOS (faltava isso!)
            return [
                'linhas_pesquisa' => $linhasPesquisa,
                'resumo' => $resumo,
                'data_atualizacao' => $dataAtualizacao,
                'formacao' => $formacao,
                'artigos' => $artigos,
                'nome_citacoes' => $nomeCitacoes,
            ];

        } catch (\Exception $e) {
            // ✅ CATCH DO TRY PRINCIPAL (faltava isso!)
            return ['erro' => 'Erro ao buscar dados do Lattes: ' . $e->getMessage()];
        }
    }

    private function destacarNome(string $autores, string $nome): string
    {
        if (empty($autores) || empty($nome)) {
            return $autores;
        }
        
        $nomeNorm = $this->normalizar($nome);
        $partes = explode(';', $autores);
        $resultado = [];
        
        foreach ($partes as $parte) {
            $parteTrim = trim($parte);
            $parteNorm = $this->normalizar($parteTrim);
            
            if (str_contains($parteNorm, $nomeNorm) || str_contains($nomeNorm, $parteNorm)) {
                $resultado[] = "<strong>{$parteTrim}</strong>";
            } else {
                $resultado[] = $parteTrim;
            }
        }
        
        return implode('; ', $resultado);
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
        return trim($texto);
    }
}