<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Equipamentos de Grande Porte - IGC USP</title>
    <style>
        /* Margem superior maior para acomodar o logotipo */
        @page { margin: 2.5cm 1.5cm 1.5cm 1.5cm; }

        /* Cabeçalho fixo: repete em todas as páginas */
        .pdf-header {
            position: fixed;
            top: -1.8cm;             /* sobe para dentro da margem superior */
            left: 0;
            right: 0;
            height: 2.5cm;
        }

        .pdf-header img {
            height: 1.6cm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333;
        }

        h1 {
            color: #1a5490;
            font-size: 15px;
            border-bottom: 2px solid #1a5490;
            padding-bottom: 5px;
        }

        h2 {
            color: #1a5490;
            font-size: 12px;
            margin: 14px 0 4px 0;
            page-break-after: avoid;
        }

        h2 .sigla {
            background: #1a5490;
            color: #fff;
            padding: 1px 6px;
        }

        h3 {
            color: #2d6da3;
            font-size: 10.5px;
            margin: 10px 0 4px 0;
            border-left: 3px solid #2d6da3;
            padding-left: 6px;
            page-break-after: avoid;
        }

        h3 .sigla {
            background: #2d6da3;
            color: #fff;
            padding: 1px 6px;
        }

        /* Cada equipamento = uma tabela (foto | dados) */
        table.equipamento {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 8px 0;
            page-break-inside: avoid; /* não corta o equipamento no meio */
        }

        table.equipamento td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top;
            background: #f9f9f9;
        }

        td.foto {
            width: 150px;
            text-align: center;
        }

        td.foto img {
            width: 130px;
            max-height: 110px;
        }

        .sem-foto {
            color: #888;
            font-size: 8px;
            padding: 20px 0;
        }

        .nome {
            font-weight: bold;
            color: #1a5490;
            font-size: 10.5px;
            margin-bottom: 4px;
        }

        .info { margin: 2px 0; }
        .info strong { color: #555; }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 6px;
            color: #888;
            font-size: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    
    <div class="pdf-header">
        <img src="{{ public_path('images/logo-igc.png') }}" alt="Instituto de Geociências - USP">
    </div>
    
    <h1>Equipamentos de Grande Porte - IGC USP</h1>

    @foreach($agrupados as $centroId => $laboratorios)
        @php
            $primeiroEquip = $laboratorios->first()->first();
            $centro = $primeiroEquip->laboratorio->centro;
        @endphp
        <h2>
            <span class="sigla">{{ $centro->sigla ?? 'SC' }}</span>
            {{ $centro->nome ?? 'Sem Centro' }}
        </h2>

        @foreach($laboratorios as $labId => $items)
            @php $lab = $items->first()->laboratorio; @endphp
            <h3>
                <span class="sigla">{{ $lab->sigla ?? 'SL' }}</span>
                {{ $lab->nome ?? 'Sem Laboratório' }}
            </h3>

            @foreach($items as $eq)
                <table class="equipamento">
                    <tr>
                        <td class="foto">
                            @if($eq->foto)
                                <img src="{{ public_path('storage/' . $eq->foto) }}">
                            @else
                                <div class="sem-foto">Sem imagem<br>disponível</div>
                            @endif
                        </td>
                        <td>
                            <div class="nome">{{ $eq->nome }}</div>
                            @if($eq->patrimonio)
                                <div class="info"><strong>Patrimônio:</strong> {{ $eq->patrimonio }}</div>
                            @endif
                            @if($eq->marca || $eq->modelo)
                                <div class="info"><strong>Marca/Modelo:</strong> {{ trim($eq->marca . ' ' . $eq->modelo) }}</div>
                            @endif
                            @if($eq->ano_aquisicao)
                                <div class="info"><strong>Ano de aquisição:</strong> {{ $eq->ano_aquisicao }}</div>
                            @endif
                            @if($eq->valor)
                                <div class="info"><strong>Valor:</strong> {{ $eq->valor_formatado }}</div>
                            @endif
                            @if($eq->financiamento)
                                <div class="info"><strong>Financiamento:</strong> {{ $eq->financiamento }}</div>
                            @endif
                            @if($eq->cod_processo_convenio)
                                <div class="info"><strong>Processo Convênio:</strong> {{ $eq->cod_processo_convenio }}</div>
                            @endif
                            @if($eq->cod_processo_incorporacao)
                                <div class="info"><strong>Processo Incorporação:</strong> {{ $eq->cod_processo_incorporacao }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            @endforeach
        @endforeach
    @endforeach

    <div class="footer">
        Última atualização: {{ date('d/m/Y \à\s H:i') }}<br>
        Gerado automaticamente pelo sistema SARaH - IGC USP
    </div>
    
    {{-- 📄 Numeração de páginas no rodapé, alinhada à direita --}}
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script(function ($PAGE_NUM, $PAGE_COUNT, $canvas, $fontMetrics) {
                $font  = $fontMetrics->get_font("DejaVu Sans", "normal");
                $size  = 8;
                $text  = "Página $PAGE_NUM de $PAGE_COUNT";

                // mede a largura do texto para alinhar à direita
                $textWidth = $fontMetrics->get_text_width($text, $font, $size);

                $x = $canvas->get_width()  - $textWidth - 40; // 40pt da borda direita (~margem)
                $y = $canvas->get_height() - 30;              // dentro da margem inferior

                $canvas->text($x, $y, $text, $font, $size, array(0.35, 0.35, 0.35));
            });
        }
    </script>
    
</body>
</html>
    
</body>
</html>