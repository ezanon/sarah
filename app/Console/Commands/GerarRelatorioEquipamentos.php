<?php

namespace App\Console\Commands;

use App\Models\Equipamento;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class GerarRelatorioEquipamentos extends Command
{
    protected $signature = 'relatorio:equipamentos';
    protected $description = 'Gera HTML estático dos equipamentos para o site público';

    public function handle()
    {
        $this->info('Buscando equipamentos...');

        // Busca apenas ativos, ordenados por centro → laboratório → ano de aquisição (mais recente primeiro)
        $equipamentos = Equipamento::with(['laboratorio.centro'])
            ->where('ativo', true)
            ->orderByRaw('(SELECT sigla FROM centros WHERE id = (SELECT centro_id FROM laboratorios WHERE id = laboratorio_id))')
            ->orderByRaw('(SELECT sigla FROM laboratorios WHERE id = laboratorio_id)')
            ->orderByRaw('ano_aquisicao IS NULL')
            ->orderBy('ano_aquisicao', 'desc')
            ->get();

        // Agrupa por centro e laboratório
        $agrupados = $equipamentos
            ->groupBy(function($eq) {
                return $eq->laboratorio->centro->id ?? 0;
            })
            ->map(function($items) {
                return $items->groupBy(function($eq) {
                    return $eq->laboratorio->id ?? 0;
                });
            });

        $this->info('Renderizando HTML...');

        $html = view('relatorios.equipamentos', compact('agrupados'))->render();

        // Salva em public/relatorios/
        $path = public_path('relatorios');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents($path . '/equipamentos.html', $html);

        $this->info('✅ Relatório gerado em: public/relatorios/equipamentos.html');
        
        // 📄 Gera o PDF (A4 retrato)
        $pdf = Pdf::loadView('relatorios.equipamentos-pdf', ['agrupados' => $agrupados])
            ->setPaper('a4', 'portrait');

        File::put(public_path('relatorios/equipamentos.pdf'), $pdf->output());

        $this->info('PDF gerado em public/relatorios/equipamentos.pdf');
    }
}