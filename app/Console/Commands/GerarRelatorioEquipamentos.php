<?php

namespace App\Console\Commands;

use App\Models\Equipamento;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GerarRelatorioEquipamentos extends Command
{
    protected $signature = 'relatorio:equipamentos';
    protected $description = 'Gera relatórios (HTML, PDF e Excel) dos equipamentos para o site público';

    public function handle()
    {
        $this->info('Buscando equipamentos...');

        // Busca apenas ativos, ordenados por centro → laboratório → tipo → nome
        $equipamentos = Equipamento::with(['laboratorio.centro', 'responsaveis'])
            ->where('ativo', true)
            ->orderByRaw('(SELECT sigla FROM centros WHERE id = (SELECT centro_id FROM laboratorios WHERE id = laboratorio_id))')
            ->orderByRaw('(SELECT sigla FROM laboratorios WHERE id = laboratorio_id)')
            // Ordena tipo: grande_porte (1) antes de periferico (0)
            ->orderByRaw("CASE WHEN tipo = 'grande_porte' THEN 1 ELSE 0 END DESC")
            ->orderBy('nome')
            ->get();

        // Agrupa por centro e laboratório (para o PDF/HTML)
        $agrupados = $equipamentos
            ->groupBy(function($eq) {
                return $eq->laboratorio->centro->id ?? 0;
            })
            ->map(function($items) {
                return $items->groupBy(function($eq) {
                    return $eq->laboratorio->id ?? 0;
                });
            });

        // Garante que a pasta de saída exista
        $path = public_path('relatorios');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // --- 1. HTML estático ---
        $this->info('Renderizando HTML...');
        $html = view('relatorios.equipamentos', compact('agrupados'))->render();
        file_put_contents($path . '/equipamentos.html', $html);
        $this->info('✅ HTML gerado em: public/relatorios/equipamentos.html');

        // --- 2. PDF (A4 retrato) ---
        $this->info('Gerando PDF...');
        $pdf = Pdf::loadView('relatorios.equipamentos-pdf', ['agrupados' => $agrupados])
            ->setPaper('a4', 'portrait');
        File::put($path . '/equipamentos.pdf', $pdf->output());
        $this->info('✅ PDF gerado em: public/relatorios/equipamentos.pdf');

        // --- 3. Excel (.xlsx) ---
        $this->info('Gerando Excel...');
        $this->gerarExcel($equipamentos, $path . '/equipamentos.xlsx');
        $this->info('✅ Excel gerado em: public/relatorios/equipamentos.xlsx');

        $this->info('🎉 Todos os relatórios foram gerados com sucesso!');
    }

    /**
     * Gera o relatório em Excel (.xlsx) com formatação profissional
     */
    private function gerarExcel($equipamentos, string $caminhoArquivo): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Equipamentos');

        // --- Cabeçalho do relatório ---
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'Relatório de Equipamentos - IGC USP');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0056b3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'Gerado em: ' . now()->format('d/m/Y \à\s H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // --- Colunas ---
        $colunas = [
            'A' => 'Centro',
            'B' => 'Laboratório',
            'C' => 'Tipo',
            'D' => 'Patrimônio',
            'E' => 'Nome',
            'F' => 'Marca',
            'G' => 'Modelo',
            'H' => 'Ano Aquisição',
            'I' => 'Ano Incorporação',
            'J' => 'Valor',
            'K' => 'Moeda',
            'L' => 'Financiamento',
            'M' => 'Proc. Convênio',
            'N' => 'Proc. Incorporação',
            'O' => 'Responsáveis',
        ];

        $linha = 4; // começa na linha 4 (depois do título)

        // Cabeçalho das colunas
        $col = 'A';
        foreach ($colunas as $letra => $titulo) {
            $sheet->setCellValue("{$letra}{$linha}", $titulo);
            $col++;
        }
        // Força as colunas de Patrimônio (D), Proc. Convênio (M) e Proc. Incorporação (N) a serem tratadas como TEXTO
        $formatoTexto = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT;
        $sheet->getStyle('D:D')->getNumberFormat()->setFormatCode($formatoTexto);
        $sheet->getStyle('M:M')->getNumberFormat()->setFormatCode($formatoTexto);
        $sheet->getStyle('N:N')->getNumberFormat()->setFormatCode($formatoTexto);

        // Estilo do cabeçalho
        $sheet->getStyle("A{$linha}:O{$linha}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0056b3'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($linha)->setRowHeight(25);
        $linha++;

        // --- Dados ---
        $linhaAtual = $linha;
        foreach ($equipamentos as $eq) {
            $responsaveis = $eq->responsaveis->pluck('name')->implode(', ') ?: '-';

            $sheet->setCellValue("A{$linha}", $eq->laboratorio->centro->sigla ?? '-');
            $sheet->setCellValue("B{$linha}", $eq->laboratorio->sigla ?? '-');
            $sheet->setCellValue("C{$linha}", $eq->tipo_formatado);
            // Força o patrimônio a ser tratado como TEXTO (não número)
            // para evitar que o Excel converta "200.123456" em número e troque o ponto por vírgula
            if (!empty($eq->patrimonio)) {
                $sheet->setCellValueExplicit(
                    "D{$linha}",
                    $eq->patrimonio,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            } else {
                $sheet->setCellValue("D{$linha}", '-');
            }
            $sheet->setCellValue("E{$linha}", $eq->nome);
            $sheet->setCellValue("F{$linha}", $eq->marca ?? '-');
            $sheet->setCellValue("G{$linha}", $eq->modelo ?? '-');
            $sheet->setCellValue("H{$linha}", $eq->ano_aquisicao ?? '-');
            $sheet->setCellValue("I{$linha}", $eq->ano_incorporacao ?? '-');
            
            // Valor como número (para permitir formatação no Excel)
            if ($eq->valor) {
                $sheet->setCellValue("J{$linha}", (float) $eq->valor);
                $sheet->getStyle("J{$linha}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            } else {
                $sheet->setCellValue("J{$linha}", '-');
            }
            
            $sheet->setCellValue("K{$linha}", $eq->moeda ?? 'BRL');
            $sheet->setCellValue("L{$linha}", $eq->financiamento ?? '-');
            $sheet->setCellValue("M{$linha}", $eq->cod_processo_convenio ?? '-');
            $sheet->setCellValue("N{$linha}", $eq->cod_processo_incorporacao ?? '-');
            $sheet->setCellValue("O{$linha}", $responsaveis);

            // Cor de fundo alternada para facilitar leitura
            if (($linha - $linhaAtual) % 2 === 0) {
                $sheet->getStyle("A{$linha}:O{$linha}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F5F5F5'],
                    ],
                ]);
            }

            // Destaca equipamentos de Grande Porte com cor azul clara
            if ($eq->tipo === 'grande_porte') {
                $sheet->getStyle("C{$linha}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);
            }

            $linha++;
        }

        // Bordas em todos os dados
        $ultimaLinha = $linha - 1;
        $sheet->getStyle("A{$linhaAtual}:O{$ultimaLinha}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // --- Largura das colunas ---
        $larguras = [
            'A' => 10, 'B' => 12, 'C' => 15, 'D' => 15, 'E' => 35,
            'F' => 18, 'G' => 20, 'H' => 14, 'I' => 16, 'J' => 14,
            'K' => 10, 'L' => 20, 'M' => 20, 'N' => 22, 'O' => 35,
        ];
        foreach ($larguras as $letra => $largura) {
            $sheet->getColumnDimension($letra)->setWidth($largura);
        }

        // --- Congela o cabeçalho (para rolar mantendo visível) ---
        $sheet->freezePane('A5');

        // --- Filtro automático (autofilter) ---
        $sheet->setAutoFilter("A{$linhaAtual}:O{$ultimaLinha}");

        // --- Salvar ---
        $writer = new Xlsx($spreadsheet);
        $writer->save($caminhoArquivo);
    }
}