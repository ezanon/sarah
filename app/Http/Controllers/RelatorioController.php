<?php

namespace App\Http\Controllers;

use App\Models\RelatorioDiretoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Uspdev\Replicado\Pessoa;
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

        // --- 1. PROJETOS DE PESQUISA ---
        $section->addText('1. Projetos de Pesquisa', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);

        foreach ($docentes as $docente) {
            $codpes = $docente['codpes'];
            $nomeDocente = $docente['nompes'] ?? 'Docente';
            $projetos = $this->buscarProjetosPorAno($codpes, $ano);

            if (!empty($projetos)) {
                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
                
                foreach ($projetos as $proj) {
                    $titulo = $proj['NOME-DO-PROJETO'] ?? '';
                    $duracao = ($proj['ANO-INICIO'] ?? '') . ' a ' . ($proj['ANO-FIM'] ?: 'Atual');
                    
                    $financiador = 'Não informada';
                    if (!empty($proj['FINANCIADORES']) && is_array($proj['FINANCIADORES'])) {
                        $financiador = $proj['FINANCIADORES'][0]['NOME-INSTITUICAO'] ?? 'Não informada';
                    }
                    $financiador = htmlspecialchars($financiador);
                    
                    $integrantes = [];
                    if (!empty($proj['EQUIPE-DO-PROJETO']) && is_array($proj['EQUIPE-DO-PROJETO'])) {
                        foreach ($proj['EQUIPE-DO-PROJETO'] as $integrante) {
                            $nome = $integrante['NOME-COMPLETO-DO-AUTOR'] ?? '';
                            $flag = $integrante['COORDENACAO'] ?? 'NAO';
                            $integrantes[] = $nome . ($flag === 'SIM' ? ' (Coordenador)' : '');
                        }
                    }
                    $integrantesStr = !empty($integrantes) ? implode(' / ', $integrantes) : 'Não informado';

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Título do Projeto: ', ['bold' => true]);
                    $textrun->addText($titulo);

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Agência Financiadora: ', ['bold' => true]);
                    $textrun->addText($financiador);

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Número do Processo: ', ['bold' => true]);

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Duração: ', ['bold' => true]);
                    $textrun->addText($duracao);

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Integrantes: ', ['bold' => true]);
                    $textrun->addText($integrantesStr);

                    $textrun = $section->addTextRun(['spaceAfter' => 250]);
                    $textrun->addText('Valor: ', ['bold' => true]);
                }
            }
        }

        // --- 2. ARTIGOS PUBLICADOS (NOVA SEÇÃO) ---
        $section->addText('2. Artigos Publicados', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);

        foreach ($docentes as $docente) {
            $codpes = $docente['codpes'];
            $nomeDocente = $docente['nompes'] ?? 'Docente';
            
            // Busca artigos do ano específico
            $artigos = $this->buscarArtigosPorAno($codpes, $ano);

            if (!empty($artigos)) {
                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
                
                foreach ($artigos as $artigo) {
                    $titulo = $artigo['TITULO-DO-ARTIGO'] ?? '';
                    $periodico = $artigo['TITULO-DO-PERIODICO-OU-REVISTA'] ?? '';
                    $volume = $artigo['VOLUME'] ?? '';
                    
                    $paginas = '';
                    if (!empty($artigo['PAGINA-INICIAL'])) {
                        $paginas = $artigo['PAGINA-INICIAL'];
                        if (!empty($artigo['PAGINA-FINAL'])) {
                            $paginas .= '-' . $artigo['PAGINA-FINAL'];
                        }
                    }
                    
                    // Extração de autores (tratando NOME-COMPLETO-DO-AUTOR como false quando vazio)
                    $autores = [];
                    if (!empty($artigo['AUTORES']) && is_array($artigo['AUTORES'])) {
                        foreach ($artigo['AUTORES'] as $autor) {
                            // NOME-COMPLETO-DO-AUTOR pode vir como false (boolean) ou string
                            $nomeCompleto = $autor['NOME-COMPLETO-DO-AUTOR'] ?? false;
                            $nomeCitacao = $autor['NOME-PARA-CITACAO'] ?? '';
                            
                            // Usa nome completo se for string válida, senão usa nome para citação
                            $nome = ($nomeCompleto && is_string($nomeCompleto) && !empty($nomeCompleto)) 
                                    ? $nomeCompleto 
                                    : $nomeCitacao;
                            
                            if (!empty($nome)) {
                                $autores[] = $nome;
                            }
                        }
                    }
                    $autoresStr = !empty($autores) ? implode('; ', $autores) : 'Não informado';

                    // 1. Título
                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
                    $textrun->addText('Título: ', ['bold' => true]);
                    $textrun->addText($titulo);

                    // 2. Autores (movido para logo após o título)
                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
                    $textrun->addText('Autores: ', ['bold' => true]);
                    $textrun->addText($autoresStr);

                    // 3. Periódico
                    $textrun = $section->addTextRun(['spaceAfter' => 80]);
                    $textrun->addText('Periódico: ', ['bold' => true]);
                    $textrun->addText($periodico);

                    // 4. Volume e Páginas (com "v." e "p.")
                    $volPagText = '';
                    if (!empty($volume)) {
                        $volPagText .= "v. {$volume}";
                    }
                    if (!empty($paginas)) {
                        $volPagText .= ($volPagText ? ', ' : '') . "p. {$paginas}";
                    }

                    if (!empty($volPagText)) {
                        $textrun = $section->addTextRun(['spaceAfter' => 200]);
                        $textrun->addText('Volume/Páginas: ', ['bold' => true]);
                        $textrun->addText($volPagText);
                    } else {
                        $section->addTextBreak(2); // Espaço caso não tenha volume/página
                    }
                }
            }
        }

        // --- DEMAIS SEÇÕES (Placeholders renumerados) ---
        $secoes = [
            '3. Cursos Extracurriculares',
            '4. Participação em Eventos Científicos e Culturais',
            '5. Participação em Conselhos Editoriais e Congêneres',
            '6. Intercâmbio Científico',
            '7. Assessoria e Consultoria',
            '8. Prêmios e Distinções',
            '9. Entrevistas para Divulgação Científica',
            '10. Patentes',
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
}