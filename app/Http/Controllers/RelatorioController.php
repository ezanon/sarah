<?php

namespace App\Http\Controllers;

use App\Models\RelatorioDiretoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Lattes;
use PhpOffice\PhpWord\PhpWord; // ✅ Importação do PHPWord

class RelatorioController extends Controller
{
    /**
     * Lista os relatórios já gerados
     */
    public function index()
    {
        $relatorios = RelatorioDiretoria::with('user')
            ->orderBy('departamento')
            ->orderByDesc('ano')
            ->get();

        return view('relatorios.index', compact('relatorios'));
    }

    /**
     * Mostra o formulário de geração (departamento + ano)
     */
    public function create()
    {
        $departamentos = config('departamentos.codigos', []);
        return view('relatorios.criar', compact('departamentos'));
    }
    
    /**
     * Gera o relatório WORD (.docx) e salva em public/relatorios/diretoria/
     */
    public function store(Request $request)
    {
        
        set_time_limit(300);
            // Aumenta para 10 minutos e 1GB de memória
    set_time_limit(600);
    ini_set('memory_limit', '1024M');
        
        \Log::info('=== INÍCIO DA GERAÇÃO DO RELATÓRIO (WORD) ===');

        $request->validate([
            'departamento' => 'required|string|max:10',
            'ano' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        $codDepto = $request->input('departamento');
        $ano = (int) $request->input('ano');
        $user = Auth::user();

        // 1. Descobrir a sigla e o nome a partir do código do departamento
        $departamentosConfig = config('departamentos.codigos', []);
        $infoDepto = $departamentosConfig[$codDepto] ?? ['sigla' => $codDepto, 'nome' => 'Departamento ' . $codDepto];
        $siglaDepto = $infoDepto['sigla'];
        $nomeDepto = $infoDepto['nome'];

        \Log::info("Código Depto: {$codDepto}, Sigla: {$siglaDepto}, Nome: {$nomeDepto}, Ano: {$ano}");

        try {
            $docentes = Pessoa::listarDocentes();
            $docentesDepto = collect($docentes)->filter(function ($docente) use ($siglaDepto) {
                return ($docente['nomabvset'] ?? '') === $siglaDepto;
            })->values();

            if ($docentesDepto->isEmpty()) {
                return back()->with('error', "Nenhum docente encontrado para o departamento {$nomeDepto} ({$siglaDepto}).");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao buscar docentes: ' . $e->getMessage());
        }

        // 2. Gera o arquivo WORD (.docx)
        \Log::info('Iniciando geração do arquivo Word...');
        try {
            $this->gerarDocxRelatorio($siglaDepto, $nomeDepto, $ano, $docentesDepto);
            \Log::info('Arquivo Word gerado com sucesso.');
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar Word: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar arquivo Word: ' . $e->getMessage());
        }

        // 3. Define o caminho e registra no banco
        $nomeArquivo = "relatorio_diretoria_{$siglaDepto}_{$ano}.docx";
        $caminhoRelativo = "relatorios/diretoria/{$nomeArquivo}";

        try {
            RelatorioDiretoria::updateOrCreate(
                ['departamento' => $siglaDepto, 'ano' => $ano],
                [
                    'caminho_arquivo' => $caminhoRelativo,
                    'gerado_em' => now(),
                    'user_id' => $user->id,
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar no banco: ' . $e->getMessage());
        }

        \Log::info('=== FIM DA GERAÇÃO DO RELATÓRIO ===');

        return redirect()
            ->route('relatorios.index')
            ->with('success', "Relatório do departamento {$nomeDepto} ({$ano}) gerado com sucesso!");
    }

    /**
     * Monta o arquivo Word (.docx) usando PHPWord com formatação refinada
     */
    private function gerarDocxRelatorio(string $siglaDepto, string $nomeDepto, int $ano, $docentes): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // --- TÍTULO INICIAL ---
        $section->addText("Relatório da Diretoria — {$nomeDepto}", ['bold' => true, 'size' => 20, 'color' => '000000', 'spaceAfter' => 100]);
        $section->addText("Departamento: {$siglaDepto} | Ano de referência: {$ano} | Gerado em: " . now()->format('d/m/Y H:i'), ['italic' => true, 'size' => 11, 'spaceAfter' => 300]);
        
        $section->addText('⚠️ Atenção: Este relatório foi gerado automaticamente. A secretaria deve revisar, complementar e formatar conforme o modelo oficial antes do envio.', ['color' => 'FF0000', 'size' => 10]);
        $section->addTextBreak(2);

//        // --- 1. PROJETOS DE PESQUISA ---
//        $section->addText('1. Projetos de Pesquisa', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//            $projetos = $this->buscarProjetosPorAno($codpes, $ano);
//
//            if (!empty($projetos)) {
//                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//                
//                foreach ($projetos as $proj) {
//                    $titulo = $proj['NOME-DO-PROJETO'] ?? '';
//                    $duracao = ($proj['ANO-INICIO'] ?? '') . ' a ' . ($proj['ANO-FIM'] ?: 'Atual');
//                    
//                    $financiador = 'Não informada';
//                    if (!empty($proj['FINANCIADORES']) && is_array($proj['FINANCIADORES'])) {
//                        $financiador = $proj['FINANCIADORES'][0]['NOME-INSTITUICAO'] ?? 'Não informada';
//                    }
//                    $financiador = htmlspecialchars($financiador);
//                    
//                    $integrantes = [];
//                    if (!empty($proj['EQUIPE-DO-PROJETO']) && is_array($proj['EQUIPE-DO-PROJETO'])) {
//                        foreach ($proj['EQUIPE-DO-PROJETO'] as $integrante) {
//                            $nome = $integrante['NOME-COMPLETO-DO-AUTOR'] ?? '';
//                            $flag = $integrante['COORDENACAO'] ?? 'NAO';
//                            $integrantes[] = $nome . ($flag === 'SIM' ? ' (Coordenador)' : '');
//                        }
//                    }
//                    $integrantesStr = !empty($integrantes) ? implode(' / ', $integrantes) : 'Não informado';
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
//                    $textrun->addText('Título do Projeto: ', ['bold' => true]);
//                    $textrun->addText($titulo);
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
//                    $textrun->addText('Agência Financiadora: ', ['bold' => true]);
//                    $textrun->addText($financiador);
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
//                    $textrun->addText('Número do Processo: ', ['bold' => true]);
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
//                    $textrun->addText('Duração: ', ['bold' => true]);
//                    $textrun->addText($duracao);
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
//                    $textrun->addText('Integrantes: ', ['bold' => true]);
//                    $textrun->addText($integrantesStr);
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 250]);
//                    $textrun->addText('Valor: ', ['bold' => true]);
//                }
//            }
//        }
//
//        // --- 2. ARTIGOS PUBLICADOS (NOVA SEÇÃO) ---
//        $section->addText('2. Artigos Publicados', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//            
//            // Busca artigos do ano específico
//            $artigos = $this->buscarArtigosPorAno($codpes, $ano);
//
//            if (!empty($artigos)) {
//                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//                
//                foreach ($artigos as $artigo) {
//                    $titulo = $this->sanitizeForWord($artigo['TITULO-DO-ARTIGO'] ?? '');
//                    $periodico = $this->sanitizeForWord($artigo['TITULO-DO-PERIODICO-OU-REVISTA'] ?? '');
//                    $volume = $artigo['VOLUME'] ?? '';
//                    
//                    $paginas = '';
//                    if (!empty($artigo['PAGINA-INICIAL'])) {
//                        $paginas = $artigo['PAGINA-INICIAL'];
//                        if (!empty($artigo['PAGINA-FINAL'])) {
//                            $paginas .= '-' . $artigo['PAGINA-FINAL'];
//                        }
//                    }
//                    
//                    // Extração de autores (tratando NOME-COMPLETO-DO-AUTOR como false quando vazio)
//                    $autores = [];
//                    if (!empty($artigo['AUTORES']) && is_array($artigo['AUTORES'])) {
//                        foreach ($artigo['AUTORES'] as $autor) {
//                            // NOME-COMPLETO-DO-AUTOR pode vir como false (boolean) ou string
//                            $nomeCompleto = $autor['NOME-COMPLETO-DO-AUTOR'] ?? false;
//                            $nomeCitacao = $autor['NOME-PARA-CITACAO'] ?? '';
//                            
//                            // Usa nome completo se for string válida, senão usa nome para citação
//                            $nome = ($nomeCompleto && is_string($nomeCompleto) && !empty($nomeCompleto)) 
//                                    ? $nomeCompleto 
//                                    : $nomeCitacao;
//                            
//                            if (!empty($nome)) {
//                                $autores[] = $nome;
//                            }
//                        }
//                    }
//                    $autoresStr = !empty($autores) ? implode('; ', $autores) : 'Não informado';
//
//                    // 1. Título
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Título: ', ['bold' => true]);
//                    $textrun->addText($titulo);
//
//                    // 2. Autores (movido para logo após o título)
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Autores: ', ['bold' => true]);
//                    $textrun->addText($autoresStr);
//
//                    // 3. Periódico
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Periódico: ', ['bold' => true]);
//                    $textrun->addText($periodico);
//
//                    // 4. Volume e Páginas (com "v." e "p.")
//                    $volPagText = '';
//                    if (!empty($volume)) {
//                        $volPagText .= "v. {$volume}";
//                    }
//                    if (!empty($paginas)) {
//                        $volPagText .= ($volPagText ? ', ' : '') . "p. {$paginas}";
//                    }
//
//                    if (!empty($volPagText)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 200]);
//                        $textrun->addText('Volume/Páginas: ', ['bold' => true]);
//                        $textrun->addText($volPagText);
//                    } else {
//                        $section->addTextBreak(2); // Espaço caso não tenha volume/página
//                    }
//                }
//            }
//        }
//        
//        // --- 3. CURSOS EXTRACURRICULARES ---
//        $section->addText('3. Cursos Extracurriculares', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//        $section->addText('3.1 Cursos Ministrados', ['bold' => true, 'size' => 13, 'color' => '333333', 'spaceBefore' => 150, 'spaceAfter' => 150]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//            
//            // Busca cursos ministrados no ano específico
//            $cursos = $this->buscarCursosMinistradosPorAno($codpes, $ano);
//
//            if (!empty($cursos)) {
//                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//                
//                foreach ($cursos as $curso) {
//                    $titulo = $curso['TITULO'] ?? '';
//                    $instituicao = $curso['INSTITUICAO-PROMOTORA-DO-CURSO'] ?? '';
//                    $nivel = $curso['NIVEL-DO-CURSO'] ?? '';
//                    
//                    // Extração de autores (mesma lógica dos artigos)
//                    $autores = [];
//                    if (!empty($curso['AUTORES']) && is_array($curso['AUTORES'])) {
//                        foreach ($curso['AUTORES'] as $autor) {
//                            $nomeCompleto = $autor['NOME-COMPLETO-DO-AUTOR'] ?? false;
//                            $nomeCitacao = $autor['NOME-PARA-CITACAO'] ?? '';
//                            
//                            $nome = ($nomeCompleto && is_string($nomeCompleto) && !empty($nomeCompleto)) 
//                                    ? $nomeCompleto 
//                                    : $nomeCitacao;
//                            
//                            if (!empty($nome)) {
//                                $autores[] = $nome;
//                            }
//                        }
//                    }
//                    $autoresStr = !empty($autores) ? implode('; ', $autores) : 'Não informado';
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Título: ', ['bold' => true]);
//                    $textrun->addText($titulo);
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Instituição Promotora: ', ['bold' => true]);
//                    $textrun->addText($instituicao ?: 'Não informada');
//
//                    if (!empty($nivel)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Nível do Curso: ', ['bold' => true]);
//                        $textrun->addText($nivel);
//                    }
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 200]);
//                    $textrun->addText('Ministrado por: ', ['bold' => true]);
//                    $textrun->addText($autoresStr);
//                }
//            }
//        }
//
//        // --- 3.2 Cursos Frequentados ---
//        $section->addText('3.2 Cursos Frequentados', ['bold' => true, 'size' => 13, 'color' => '333333', 'spaceBefore' => 150, 'spaceAfter' => 150]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//
//            // Busca cursos frequentados do ano específico
//            $cursos = $this->buscarCursosFrequentadosPorAno($codpes, $ano);
//
//            if (!empty($cursos)) {
//                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//
//                foreach ($cursos as $curso) {
//                    $titulo = $curso['NOME-CURSO'] ?? '';
//                    $instituicao = $curso['NOME-INSTITUICAO'] ?? '';
//                    $cargaHoraria = $curso['CARGA-HORARIA'] ?? '';
//                    $status = $curso['STATUS-DO-CURSO'] ?? '';
//                    $tipo = $curso['TIPO'] ?? '';
//
//                    // Título do curso
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Curso: ', ['bold' => true]);
//                    $textrun->addText($titulo);
//
//                    // Instituição
//                    if (!empty($instituicao)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Instituição: ', ['bold' => true]);
//                        $textrun->addText($instituicao);
//                    }
//
//                    // Carga horária
//                    if (!empty($cargaHoraria)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Carga Horária: ', ['bold' => true]);
//                        $textrun->addText($cargaHoraria . ' horas');
//                    }
//
//                    // Status
//                    if (!empty($status)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 200]);
//                        $textrun->addText('Status: ', ['bold' => true]);
//                        $textrun->addText($status);
//                    } else {
//                        $section->addTextBreak(1);
//                    }
//                }
//            }
//        }
//        
//        // --- 4. PARTICIPAÇÃO EM EVENTOS CIENTÍFICOS E CULTURAIS ---
//        $section->addText('4. Participação em Eventos Científicos e Culturais', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//            
//            // Busca participações em eventos do ano específico
//            $eventos = $this->buscarEventosPorAno($codpes, $ano);
//
//            if (!empty($eventos)) {
//                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//                
//                foreach ($eventos as $evento) {
//                    $titulo = $this->sanitizeForWord($evento['TITULO'] ?? '');
//                    $nomeEvento = $this->sanitizeForWord($evento['NOME-DO-EVENTO'] ?? '');
//                    $instituicao = $this->sanitizeForWord($evento['NOME-INSTITUICAO'] ?? '');
//                    $cidade = $this->sanitizeForWord($evento['CIDADE-DO-EVENTO'] ?? '');
//                    $tipoParticipacao = $this->sanitizeForWord($evento['TIPO-PARTICIPACAO'] ?? '');
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Título: ', ['bold' => true]);
//                    $textrun->addText($titulo);
//
//                    if (!empty($nomeEvento)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Evento: ', ['bold' => true]);
//                        $textrun->addText($nomeEvento);
//                    }
//
//                    if (!empty($instituicao)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Instituição: ', ['bold' => true]);
//                        $textrun->addText($instituicao);
//                    }
//
//                    if (!empty($cidade)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Local: ', ['bold' => true]);
//                        $textrun->addText($cidade);
//                    }
//
//                    if (!empty($tipoParticipacao)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 200]);
//                        $textrun->addText('Tipo de Participação: ', ['bold' => true]);
//                        $textrun->addText($tipoParticipacao);
//                    } else {
//                        $section->addTextBreak(1);
//                    }
//                }
//            }
//        }
//
//        // --- 5. PARTICIPAÇÃO EM CONSELHOS EDITORIAIS E CONGÊNERES ---
//        \Log::info('Iniciando seção 5 - Conselhos Editoriais');
//        $section->addText('5. Participação em Conselhos Editoriais e Congêneres', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//
//            \Log::info("Buscando conselhos editoriais para {$nomeDocente} (codpes: {$codpes})");
//
//            try {
//                $atuacoes = $this->buscarCorposEditoriaisPorAno($codpes, $ano);
//                \Log::info("Retornou " . (is_array($atuacoes) ? count($atuacoes) : '0') . " atuações");
//            } catch (\Exception $e) {
//                \Log::error("Erro ao buscar conselhos para {$nomeDocente}: " . $e->getMessage());
//                continue;
//            }
//
//            if (!empty($atuacoes)) {
//                $section->addText($this->sanitizeForWord($nomeDocente), ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//
//            foreach ($atuacoes as $index => $atuacao) {
//                try {
//                    // Aplica sanitização em todos os campos para remover & soltos, espaços extras e caracteres de controle
//                    $periodico = $this->sanitizeForWord($atuacao['NOME-PERIODICO'] ?? '');
//                    $tipoVinculo = $this->sanitizeForWord($atuacao['TIPO-VINCULO'] ?? '');
//                    $periodoInicio = $this->sanitizeForWord($atuacao['PERIODO-INICIO'] ?? '');
//                    $periodoFim = $this->sanitizeForWord($atuacao['PERIODO-FIM'] ?? '');
//
//                    \Log::info("Processando atuação {$index} para {$nomeDocente}: {$periodico}");
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Periódico/Órgão: ', ['bold' => true]);
//                    $textrun->addText($periodico);
//
//                    if (!empty($tipoVinculo)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Tipo de Vínculo: ', ['bold' => true]);
//                        $textrun->addText($tipoVinculo);
//                    }
//
//                    if (!empty($periodoInicio) || !empty($periodoFim)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 200]);
//                        $textrun->addText('Período: ', ['bold' => true]);
//                        // Garante que não haja " / atual" ou algo quebrado
//                        $periodoFormatado = trim("{$periodoInicio} a {$periodoFim}", " a");
//                        $textrun->addText($periodoFormatado);
//                    } else {
//                        $section->addTextBreak(1);
//                    }
//                } catch (\Exception $e) {
//                    \Log::error("Erro ao processar atuação {$index} para {$nomeDocente}: " . $e->getMessage());
//                    \Log::error("Dados brutos da atuação: " . json_encode($atuacao));
//                    continue;
//                }
//            }
//            }
//        }
//        \Log::info('Finalizada seção 5 - Conselhos Editoriais');       
//
//        // --- 8. PRÊMIOS E DISTINÇÕES ---
//        $section->addText('8. Prêmios e Distinções', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//            
//            // A biblioteca retorna um array de strings formatadas ("Nome - Ano: XXXX") ou false
//            $premios = \Uspdev\Replicado\Lattes::listarPremios($codpes);
//
//            if (!empty($premios) && is_array($premios)) {
//                // Filtra apenas os prêmios do ano de referência do relatório
//                $premiosDoAno = array_filter($premios, function($premioStr) use ($ano) {
//                    return str_contains($premioStr, "- Ano: {$ano}");
//                });
//
//                if (!empty($premiosDoAno)) {
//                    $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//                    
//                    foreach ($premiosDoAno as $premioStr) {
//                        // Separa a string "Título do Prêmio - Ano: 2023" em duas partes
//                        $partes = explode(' - Ano: ', $premioStr);
//                        $titulo = trim($partes[0] ?? 'Não informado');
//                        $anoPremio = trim($partes[1] ?? '');
//
//                        // Linha 1: Título em negrito
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Título: ', ['bold' => true]);
//                        $textrun->addText($titulo);
//
//                        // Linha 2: Ano em negrito + espaçamento maior no final do bloco
//                        if (!empty($anoPremio)) {
//                            $textrun = $section->addTextRun(['spaceAfter' => 200]); 
//                            $textrun->addText('Ano: ', ['bold' => true]);
//                            $textrun->addText($anoPremio);
//                        } else {
//                            $section->addTextBreak(1); // Espaçamento caso não tenha ano
//                        }
//                    }
//                }
//            }
//        }       
//        
//        // --- 9. ENTREVISTAS PARA DIVULGAÇÃO CIENTÍFICA ---
//        $section->addText('9. Entrevistas para Divulgação Científica', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $docente['nompes'] ?? 'Docente';
//            
//            // Busca entrevistas/programas de TV/Rádio do ano específico
//            $entrevistas = $this->buscarEntrevistasPorAno($codpes, $ano);
//
//            if (!empty($entrevistas)) {
//                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//                
//                foreach ($entrevistas as $entrevista) {
//                    $titulo = $this->sanitizeForWord($entrevista['TITULO'] ?? '');
//                    $emissora = $this->sanitizeForWord($entrevista['EMISSORA'] ?? '');
//                    $tema = $this->sanitizeForWord($entrevista['TEMA'] ?? '');
//                    $data = $entrevista['DATA-DA-APRESENTACAO'] ?? '';
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Título: ', ['bold' => true]);
//                    $textrun->addText($titulo);
//
//                    if (!empty($emissora)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Emissora/Meio: ', ['bold' => true]);
//                        $textrun->addText($emissora);
//                    }
//
//                    if (!empty($tema)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Tema: ', ['bold' => true]);
//                        $textrun->addText($tema);
//                    }
//
//                    if (!empty($data)) {
//                        // Formata a data do Lattes (ddmmyyyy) para dd/mm/yyyy
//                        if (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $data, $matches)) {
//                            $data = "{$matches[1]}/{$matches[2]}/{$matches[3]}";
//                        }
//                        $textrun = $section->addTextRun(['spaceAfter' => 200]);
//                        $textrun->addText('Data: ', ['bold' => true]);
//                        $textrun->addText($data);
//                    } else {
//                        $section->addTextBreak(2); // Espaço caso não tenha data
//                    }
//                }
//            }
//        } 
//
//        // --- 10. PATENTES ---
//        $section->addText('10. Patentes', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
//
//        $temPatentesNoAno = false; // Flag para verificar se há pelo menos uma patente no ano
//
//        foreach ($docentes as $docente) {
//            $codpes = $docente['codpes'];
//            $nomeDocente = $this->sanitizeForWord($docente['nompes'] ?? 'Docente');
//            
//            // Busca patentes do ano específico
//            $patentes = $this->buscarPatentesPorAno($codpes, $ano);
//
//            if (!empty($patentes)) {
//                $temPatentesNoAno = true;
//                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
//                
//                foreach ($patentes as $patente) {
//                    $titulo = $this->sanitizeForWord($patente['TITULO'] ?? '');
//                    $finalidade = $this->sanitizeForWord($patente['FINALIDADE'] ?? '');
//                    $instituicao = $this->sanitizeForWord($patente['INSTITUICAO-FINANCIADORA'] ?? '');
//                    $anoPatente = $this->sanitizeForWord($patente['ANO'] ?? ($patente['DATA-DE-PEDIDO-DE-DEPOSITO'] ?? ''));
//
//                    // Extração de autores (mesma lógica segura e sanitizada)
//                    $autores = [];
//                    if (!empty($patente['AUTORES']) && is_array($patente['AUTORES'])) {
//                        foreach ($patente['AUTORES'] as $autor) {
//                            $nomeCompleto = $autor['NOME-COMPLETO-DO-AUTOR'] ?? false;
//                            $nomeCitacao = $autor['NOME-PARA-CITACAO'] ?? '';
//                            
//                            $nome = ($nomeCompleto && is_string($nomeCompleto) && !empty($nomeCompleto)) 
//                                    ? $nomeCompleto 
//                                    : $nomeCitacao;
//                            
//                            if (!empty($nome)) {
//                                $autores[] = $this->sanitizeForWord($nome);
//                            }
//                        }
//                    }
//                    $autoresStr = !empty($autores) ? implode('; ', $autores) : 'Não informado';
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Título: ', ['bold' => true]);
//                    $textrun->addText($titulo);
//
//                    if (!empty($finalidade)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Finalidade: ', ['bold' => true]);
//                        $textrun->addText($finalidade);
//                    }
//
//                    if (!empty($instituicao)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                        $textrun->addText('Instituição Financiadora: ', ['bold' => true]);
//                        $textrun->addText($instituicao);
//                    }
//
//                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
//                    $textrun->addText('Autores: ', ['bold' => true]);
//                    $textrun->addText($autoresStr);
//
//                    if (!empty($anoPatente)) {
//                        $textrun = $section->addTextRun(['spaceAfter' => 200]);
//                        $textrun->addText('Ano/Data: ', ['bold' => true]);
//                        $textrun->addText($anoPatente);
//                    } else {
//                        $section->addTextBreak(1);
//                    }
//                }
//            }
//        }
//        // Se nenhum docente teve patentes no ano, exibe mensagem
//        if (!$temPatentesNoAno) {
//            $section->addText("Nenhum registro de patente encontrado para o ano de {$ano}.", ['italic' => true, 'color' => '888888', 'spaceAfter' => 200]);
//        }
        
        
        // --- 11. CATEGORIA DE PESQUISADOR CNPq ---
        $section->addText('11. Categoria de Pesquisador CNPq', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);

        // Busca as categorias CNPq de todos os docentes do departamento de uma vez só (otimização)
        $codpesLista = $docentes->pluck('codpes')->toArray();
        $categoriasCnpq = $this->buscarCategoriasCnpq($codpesLista);

        // Mapeamento dos níveis (espelhando a constante do CnpqController)
        $mapeamentoNiveis = [
            '1A' => 'PQ-1A',
            '1B' => 'PQ-1B',
            '1C' => 'PQ-1C',
            '1D' => 'PQ-1D',
            '2'  => 'PQ-2',
            'DT' => 'DT (Desenvolvimento Tecnológico)',
        ];

        // Cria uma tabela sem bordas para alinhar: categoria à esquerda, nome à direita
        $tabela = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 80,
            'width' => 9000,
        ]);

        $temAlgumaCategoria = false;

        foreach ($docentes as $docente) {
            $codpes = $docente['codpes'];
            $nomeDocente = $this->sanitizeForWord($docente['nompes'] ?? 'Docente');
            $nivelBruto = $categoriasCnpq[$codpes] ?? null;

            // Só exibe se o docente tiver categoria CNPq cadastrada
            if ($nivelBruto && isset($mapeamentoNiveis[$nivelBruto])) {
                $temAlgumaCategoria = true;
                $categoriaFormatada = $mapeamentoNiveis[$nivelBruto];

                $linha = $tabela->addRow();

                // Coluna 1: Categoria CNPq (alinhada à esquerda, em negrito e azul)
                $celulaCat = $linha->addCell(1500, ['width' => 1500]);
                $celulaCat->addText($categoriaFormatada, ['bold' => true, 'size' => 11, 'color' => '0056b3']);

                // Coluna 2: Nome do docente (alinhado à esquerda)
                $celulaNome = $linha->addCell(7500, ['width' => 7500]);
        $celulaNome->addText($nomeDocente, ['size' => 11]);
    }
}

// Se nenhum docente tiver categoria CNPq
if (!$temAlgumaCategoria) {
    $section->addText("Nenhum docente com bolsa Pesquisador CNPq cadastrada no sistema.", ['italic' => true, 'color' => '888888', 'spaceAfter' => 200]);
}
        
        // --- 6. Participação em Conselhos Editoriais e Congêneres ---
        $section->addText('6. Intercâmbio Científico', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
        $section->addText('[ C O M P L E T E ]', ['italic' => true, 'color' => 'FF0000', 'bold' => true]); 
        
        // --- 7. Participação em Conselhos Editoriais e Congêneres ---
        $section->addText('7. Assessoria e Consultoria', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
        $section->addText('[ C O M P L E T E ]', ['italic' => true, 'color' => 'FF0000', 'bold' => true]); 
        
        // --- DEMAIS SEÇÕES (Placeholders renumerados) ---
        $secoes = [
            '11. Categoria de Pesquisador CNPq',
        ];

        foreach ($secoes as $tituloSecao) {
            $section->addText($tituloSecao, ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
            $section->addText('(Em desenvolvimento)', ['italic' => true]);
        }

        // --- 12. CONSELHO DE DEPARTAMENTO ---
        $section->addText('12. Composição do Conselho de Departamento', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
        $section->addText('⚠️ Campo para preenchimento manual pela secretaria.', ['italic' => true, 'color' => 'FF0000', 'spaceAfter' => 200]);

        // --- RODAPÉ ---
        $section->addTextBreak(3);
        $section->addText('Relatório gerado pelo sistema SARAH em ' . now()->format('d/m/Y H:i:s'), ['size' => 8, 'color' => '888888', 'align' => 'center']);

        // --- SALVAR ARQUIVO ---
        $nomeArquivo = "relatorio_diretoria_{$siglaDepto}_{$ano}.docx";
        $diretorio = public_path('relatorios/diretoria');

        if (!File::exists($diretorio)) {
            File::makeDirectory($diretorio, 0775, true);
        }

        $caminhoCompleto = $diretorio . '/' . $nomeArquivo;
        $phpWord->save($caminhoCompleto, 'Word2007');
    }

    /**
     * Busca artigos publicados no Lattes e filtra pelo ano selecionado
     */
    private function buscarArtigosPorAno(string $codpes, int $ano): array
    {
        try {
            // Usa tipo 'periodo' para filtrar apenas o ano específico
            $artigos = \Uspdev\Replicado\Lattes::listarArtigos($codpes, null, 'periodo', $ano, $ano);
            
            return $artigos ?: [];

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar artigos para {$codpes}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca projetos de pesquisa no Lattes e filtra os que estavam ativos no ano selecionado
     */
    private function buscarProjetosPorAno(string $codpes, int $ano): array
    {
        try {
            $projetos = \Uspdev\Replicado\Lattes::listarProjetosPesquisa($codpes, null, 'registros', 9999);
            
            if (empty($projetos)) {
                return [];
            }

            return array_filter($projetos, function($proj) use ($ano) {
                $inicio = (int) ($proj['ANO-INICIO'] ?? 0);
                $fim = (int) ($proj['ANO-FIM'] ?: 9999); 
                return ($inicio <= $ano && $fim >= $ano);
            });

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar projetos para {$codpes}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca cursos de curta duração ministrados no Lattes e filtra pelo ano selecionado
     */
    private function buscarCursosMinistradosPorAno(string $codpes, int $ano): array
    {
        try {
            // Usa tipo 'periodo' para filtrar apenas o ano específico
            $cursos = \Uspdev\Replicado\Lattes::listarCursosCurtaDuracao($codpes, null, 'periodo', $ano, $ano);
            
            return $cursos ?: [];

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar cursos ministrados para {$codpes}: " . $e->getMessage());
            return [];
        }
    } 
    
    /**
     * Busca entrevistas e programas de rádio/TV no Lattes e filtra pelo ano selecionado
     */
    private function buscarEntrevistasPorAno(string $codpes, int $ano): array
    {
        try {
            // Usa tipo 'periodo' para filtrar apenas o ano específico
            $entrevistas = \Uspdev\Replicado\Lattes::listarRadioTV($codpes, null, 'periodo', $ano, $ano);
            
            return $entrevistas ?: [];

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar entrevistas para {$codpes}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca cursos frequentados (formação complementar) no Lattes e filtra pelo ano selecionado
     */
    private function buscarCursosFrequentadosPorAno(string $codpes, int $ano): array
    {
        try {
            // Usa o novo método que percorre todas as tags de FORMACAO-COMPLEMENTAR
            $cursos = Lattes::listarFormacaoComplementar($codpes, $ano, $ano);

            return $cursos ?: [];

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar cursos frequentados para {$codpes}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca participações em eventos no Lattes e filtra pelo ano selecionado
     */
    private function buscarEventosPorAno(string $codpes, int $ano): array
    {
        try {
            // Usa o novo método que percorre as tags de PARTICIPACAO-EM-EVENTOS-CONGRESSOS
            $eventos = \Uspdev\Replicado\Lattes::listarParticipacaoEventos($codpes, $ano, $ano);
            
            return $eventos ?: [];

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar eventos para {$codpes}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca atuações em corpos editoriais (revisor, membro de corpo editorial) 
     * que estavam ativas no ano selecionado
     */
    private function buscarCorposEditoriaisPorAno(string $codpes, int $ano): array
    {
        try {
            // O método da biblioteca já filtra por sobreposição de período
            $atuacoes = \Uspdev\Replicado\Lattes::listarAtuacaoEmCorposEditoriais($codpes, $ano, $ano);
            
            return $atuacoes ?: [];

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar corpos editoriais para {$codpes}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Sanitiza texto para o PHPWord, garantindo que o XML interno não seja corrompido
     */
    private function sanitizeForWord($text): string
    {
        if (empty($text)) {
            return '';
        }

        $text = (string) $text;

        // 1. Garante que a string seja UTF-8 válido (remove ou substitui caracteres inválidos)
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // 2. Remove caracteres de controle invisíveis (nulos, tabs estranhos) que quebram o XML
        $text = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // 3. Decodifica entidades HTML existentes para padronizar 
        // (ex: transforma "&amp;" de volta em "&" para evitar "double encoding")
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // 4. Escapamento manual e seguro para XML.
        // Garantimos que o "&" vire "&amp;", "<" vire "&lt;", etc.
        $text = str_replace('&', '&amp;', $text);
        $text = str_replace('<', '&lt;', $text);
        $text = str_replace('>', '&gt;', $text);

        // 5. Limita o tamanho para evitar estouro de memória em descrições gigantes
        if (strlen($text) > 10000) {
            $text = substr($text, 0, 10000) . '...';
        }

        return $text;
    }
    
    /**
     * Busca patentes no Lattes e filtra manualmente pelo ano selecionado
     * !!! corrigir a Lattes::listarPatentes que está desconsiderando o período solicitado
     */
    private function buscarPatentesPorAno(string $codpes, int $ano): array
    {
        try {
            // Busca todas as patentes (sem filtro de ano na biblioteca)
            $todasPatentes = \Uspdev\Replicado\Lattes::listarPatentes($codpes);

            if (empty($todasPatentes) || !is_array($todasPatentes)) {
                return [];
            }

            // Filtra manualmente apenas as patentes do ano específico
            $patentesDoAno = array_filter($todasPatentes, function($patente) use ($ano) {
                $anoPatente = (int) ($patente['ANO'] ?? 0);
                return $anoPatente === $ano;
            });

            return array_values($patentesDoAno); // Reindexa o array

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar patentes para {$codpes}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca as categorias de Pesquisador CNPq cadastradas no sistema SARAH
     * para uma lista de códigos de pessoa.
     *
     * @param array $codpesLista Lista de codpes
     * @return array Array associativo [codpes => nivel_cnpq]
     */
    private function buscarCategoriasCnpq(array $codpesLista): array
    {
        if (empty($codpesLista)) {
            return [];
        }

        try {
            $resultados = \App\Models\User::whereIn('codpes', $codpesLista)
                ->whereNotNull('nivel_cnpq')
                ->select('codpes', 'nivel_cnpq')
                ->get();

            $categorias = [];
            foreach ($resultados as $user) {
                $categorias[$user->codpes] = $user->nivel_cnpq;
            }

            return $categorias;

        } catch (\Exception $e) {
            \Log::error("Erro ao buscar categorias CNPq: " . $e->getMessage());
            return [];
        }
    }
    
}