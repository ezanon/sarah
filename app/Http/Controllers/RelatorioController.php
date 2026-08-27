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

        // --- TÍTULO INICIAL (Bem destacado) ---
        $section->addText("Relatório da Diretoria — {$nomeDepto}", ['bold' => true, 'size' => 20, 'color' => '000000', 'spaceAfter' => 100]);
        $section->addText("Departamento: {$siglaDepto} | Ano de referência: {$ano} | Gerado em: " . now()->format('d/m/Y H:i'), ['italic' => true, 'size' => 11, 'spaceAfter' => 300]);
        
        $section->addText('⚠️ Atenção: Este relatório foi gerado automaticamente. A secretaria deve revisar, complementar e formatar conforme o modelo oficial antes do envio.', ['color' => 'FF0000', 'size' => 10]);
        $section->addTextBreak(2);

        // --- 1. PROJETOS DE PESQUISA ---
        // Destaque para a seção (Tamanho maior e cor azul)
        $section->addText('1. Projetos de Pesquisa', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);

        foreach ($docentes as $docente) {
            $codpes = $docente['codpes'];
            $nomeDocente = $docente['nompes'] ?? 'Docente';
            $projetos = $this->buscarProjetosPorAno($codpes, $ano);

            if (!empty($projetos)) {
                // Destaque para o nome do docente (Tamanho 14, negrito e sublinhado)
                $section->addText($nomeDocente, ['bold' => true, 'size' => 14, 'color' => '333333', 'spaceBefore' => 200, 'spaceAfter' => 100, 'underline' => 'single']);
                
                foreach ($projetos as $proj) {
                    $titulo = $proj['NOME-DO-PROJETO'] ?? '';
                    $duracao = ($proj['ANO-INICIO'] ?? '') . ' a ' . ($proj['ANO-FIM'] ?: 'Atual');
                    
                    $financiador = 'Não informada';
                    if (!empty($proj['FINANCIADORES']) && is_array($proj['FINANCIADORES'])) {
                        $financiador = $proj['FINANCIADORES'][0]['NOME-INSTITUICAO'] ?? 'Não informada';
                    }
                    
                    $integrantes = [];
                    if (!empty($proj['EQUIPE-DO-PROJETO']) && is_array($proj['EQUIPE-DO-PROJETO'])) {
                        foreach ($proj['EQUIPE-DO-PROJETO'] as $integrante) {
                            $nome = $integrante['NOME-COMPLETO-DO-AUTOR'] ?? '';
                            $flag = $integrante['COORDENACAO'] ?? 'NAO';
                            $integrantes[] = $nome . ($flag === 'SIM' ? ' (Coordenador)' : '');
                        }
                    }
                    $integrantesStr = !empty($integrantes) ? implode(' / ', $integrantes) : 'Não informado';

                    // Uso de addTextRun para deixar APENAS o nome do campo em negrito
                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Título do Projeto: ', ['bold' => true]);
                    $textrun->addText($titulo);

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Agência Financiadora: ', ['bold' => true]);
                    $textrun->addText($financiador);

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Número do Processo: ', ['bold' => true]);
                    // Linhas removidas: a secretaria clicará após os dois pontos e digitará

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Duração: ', ['bold' => true]);
                    $textrun->addText($duracao);

                    $textrun = $section->addTextRun(['spaceAfter' => 100]);
                    $textrun->addText('Integrantes: ', ['bold' => true]);
                    $textrun->addText($integrantesStr);

                    $textrun = $section->addTextRun(['spaceAfter' => 250]); // Espaço maior antes do próximo projeto
                    $textrun->addText('Valor: ', ['bold' => true]);
                    // Linhas removidas
                }
            }
        }

        // --- DEMAIS SEÇÕES (Placeholders) ---
        $secoes = [
            '2. Cursos Extracurriculares',
            '3. Participação em Eventos Científicos e Culturais',
            '4. Participação em Conselhos Editoriais e Congêneres',
            '5. Intercâmbio Científico',
            '6. Assessoria e Consultoria',
            '7. Prêmios e Distinções',
            '8. Entrevistas para Divulgação Científica',
            '9. Patentes',
            '10. Categoria de Pesquisador CNPq',
        ];

        foreach ($secoes as $tituloSecao) {
            $section->addText($tituloSecao, ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
            $section->addText('(Em desenvolvimento)', ['italic' => true]);
        }

        // --- 11. CONSELHO DE DEPARTAMENTO ---
        $section->addText('11. Composição do Conselho de Departamento', ['bold' => true, 'size' => 16, 'color' => '0056b3', 'spaceBefore' => 300, 'spaceAfter' => 200]);
        // Apenas a informação de preenchimento manual, sem as linhas de titulares/suplentes
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