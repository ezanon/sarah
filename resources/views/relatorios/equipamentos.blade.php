<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Equipamentos de Grande Porte - IGC USP</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.5;
        }
        h1 {
            color: #1a5490;
            border-bottom: 3px solid #1a5490;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        h2 {
            color: #1a5490;
            margin-top: 40px;
            margin-bottom: 10px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        h2 .centro-sigla {
            background: #1a5490;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
        }
        h2 .centro-nome {
            font-weight: 600;
            font-size: 1.5rem;
            color: #1a5490;
        }

        h3 {
            color: #2d6da3;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 1.2rem;
            padding-left: 10px;
            border-left: 4px solid #2d6da3;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        h3 .lab-sigla {
            background: #2d6da3;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        h3 .lab-nome {
            font-weight: 600;
            font-size: 1.2rem;
            color: #2d6da3;
        }
        .equipamento {
            display: flex;
            gap: 20px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-left: 4px solid #1a5490;
            border-radius: 4px;
            align-items: flex-start;
            overflow: hidden; /* ✅ Evita vazamento */
        }

        .equipamento-foto {
            flex-shrink: 0;
            width: 220px;
            text-align: center;
        }

        .equipamento-foto img {
            max-width: 100%;
            max-height: 170px;
            border-radius: 4px;
            border: 1px solid #ddd;
            cursor: zoom-in;
            transition: transform 0.2s;
        }

        .equipamento-foto img:hover {
            transform: scale(1.03);
        }

        .sem-foto {
            width: 220px;
            height: 170px;
            background: #e9ecef;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 0.85rem;
            text-align: center;
            padding: 10px;
            border: 1px dashed #bbb;
            box-sizing: border-box; /* ✅ Garante que padding não aumente a largura */
        }

        .equipamento-dados {
            flex: 1;
            min-width: 0; /* ✅ Permite que o texto quebre linha */
            overflow-wrap: break-word; /* ✅ Quebra palavras longas */
            word-wrap: break-word;
        }

        .equipamento-nome {
            font-size: 1.15rem;
            font-weight: bold;
            color: #1a5490;
            margin: 0 0 8px 0;
            word-break: break-word;
        }

        .equipamento-info {
            margin: 4px 0;
            font-size: 0.95rem;
            display: flex;
            flex-wrap: wrap; /* ✅ Permite quebra de linha nos campos */
            gap: 4px;
        }

        .equipamento-info strong {
            color: #555;
            min-width: 160px;
            flex-shrink: 0;
        }

        .patrimonio {
            font-family: monospace;
            background: #fff;
            padding: 2px 8px;
            border-radius: 3px;
            border: 1px solid #ddd;
            font-size: 0.9rem;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #888;
            font-size: 0.85rem;
            text-align: center;
        }

        /* 🖼️ Estilos do Lightbox (Zoom) */
        .lightbox-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            justify-content: center;
            align-items: center;
            cursor: zoom-out;
        }
        .lightbox-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        .lightbox-close {
            position: absolute;
            top: 20px; right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
        }
        .lightbox-close:hover {
            color: #ccc;
        }

        @media (max-width: 600px) {
            .equipamento {
                flex-direction: column;
            }
            .equipamento-foto, .sem-foto {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>🔬 Equipamentos de Grande Porte - IGC USP</h1>

    @foreach($agrupados as $centroId => $laboratorios)
            @php
                $primeiroEquip = $laboratorios->first()->first();
                $centro = $primeiroEquip->laboratorio->centro;
            @endphp
            <h2>
                <span class="centro-sigla">{{ $centro->sigla ?? 'SC' }}</span>
                <span class="centro-nome">{{ $centro->nome ?? 'Sem Centro' }}</span>
            </h2>

            @foreach($laboratorios as $labId => $items)
                @php
                    $primeiroLab = $items->first();
                    $lab = $primeiroLab->laboratorio;
                @endphp
                <h3>
                    <span class="lab-sigla">{{ $lab->sigla ?? 'SL' }}</span>
                    <span class="lab-nome">{{ $lab->nome ?? 'Sem Laboratório' }}</span>
                </h3>

            @foreach($items as $eq)
                <div class="equipamento">
                    <div class="equipamento-foto">
                        @if($eq->foto)
                            <img src="{{ asset('storage/' . $eq->foto) }}"
                                 alt="{{ $eq->nome }}"
                                 onclick="openLightbox(this.src)">
                        @else
                            <div class="sem-foto">
                                <div style="text-align: center;">
                                    <div style="font-size: 2rem; margin-bottom: 8px;">📷</div>
                                    <span>Sem imagem<br>disponível</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="equipamento-dados">
                        <div class="equipamento-nome">{{ $eq->nome }}</div>
                        @if($eq->patrimonio)
                            <div class="equipamento-info">
                                <strong>Patrimônio:</strong>
                                <span class="patrimonio">{{ $eq->patrimonio }}</span>
                            </div>
                        @endif
                        @if($eq->marca || $eq->modelo)
                            <div class="equipamento-info">
                                <strong>Marca/Modelo:</strong>
                                {{ trim($eq->marca . ' ' . $eq->modelo) }}
                            </div>
                        @endif
                        @if($eq->ano_aquisicao)
                            <div class="equipamento-info">
                                <strong>Ano de aquisição:</strong> {{ $eq->ano_aquisicao }}
                            </div>
                        @endif
                        @if($eq->valor)
                            <div class="equipamento-info">
                                <strong>Valor:</strong> R$ {{ number_format($eq->valor, 2, ',', '.') }}
                            </div>
                        @endif
                        @if($eq->financiamento)
                            <div class="equipamento-info">
                                <strong>Financiamento:</strong> {{ $eq->financiamento }}
                            </div>
                        @endif
                        @if($eq->cod_processo_convenio)
                            <div class="equipamento-info">
                                <strong>Processo Convênio:</strong> {{ $eq->cod_processo_convenio }}
                            </div>
                        @endif
                        @if($eq->cod_processo_incorporacao)
                            <div class="equipamento-info">
                                <strong>Processo Incorporação:</strong> {{ $eq->cod_processo_incorporacao }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endforeach
    @endforeach

    <div class="footer">
        Última atualização: {{ date('d/m/Y \à\s H:i') }}<br>
        Gerado automaticamente pelo sistema SARaH - IGC USP
    </div>

    <!-- 🖼️ Estrutura do Lightbox -->
    <div id="lightbox" class="lightbox-overlay" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-img" src="">
    </div>

    <!-- 🖼️ Script do Lightbox -->
    <script>
        function openLightbox(src) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox').style.display = 'flex';
        }
        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
            document.getElementById('lightbox-img').src = '';
        }
    </script>
</body>
</html>