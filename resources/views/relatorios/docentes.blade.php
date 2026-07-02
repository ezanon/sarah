<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docentes - IGC USP</title>
    <style>
        .relatorio-docentes {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.5;
            box-sizing: border-box;
        }

        .relatorio-docentes *, .relatorio-docentes *::before, .relatorio-docentes *::after {
            box-sizing: inherit;
        }

        .relatorio-docentes h1 {
            color: #1a5490;
            border-bottom: 3px solid #1a5490;
            padding-bottom: 10px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2rem;
        }

        .relatorio-docentes .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        /* Grid de cards */
        .docentes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .docente-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .docente-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .docente-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        /* Foto 3x4 */
        .docente-foto {
            flex-shrink: 0;
            width: 90px;
            height: 120px;
            border-radius: 4px;
            overflow: hidden;
            border: 2px solid #1a5490;
            background: #f5f5f5;
        }

        .docente-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sem-foto {
            width: 100%;
            height: 100%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 2rem;
        }

        .docente-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        /* Badge do departamento acima do nome */
        .docente-depto-badge {
            display: inline-block;
            align-self: flex-start;
            background: #1a5490;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .docente-nome {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a5490;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }

        .docente-email {
            margin-top: 2px;
            font-size: 0.85rem;
            color: #555;
            word-break: break-all;
        }

        .docente-email a {
            color: #1a5490;
            text-decoration: none;
        }

        .docente-email a:hover {
            text-decoration: underline;
        }

        /* Container de Links e ODS */
        .docente-links-section {
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
        }

        .docente-links-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            margin-bottom: 8px;
        }

        .docente-links-row:last-child {
            margin-bottom: 0;
        }

        /* Links acadêmicos (pílulas com ícone + texto) */
        .docente-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }

        .docente-link:hover {
            background: #e9ecef;
            border-color: #dee2e6;
            transform: translateY(-1px);
        }

        .docente-link img {
            width: 16px;
            height: 16px;
            object-fit: contain;
        }

        /* ODS (apenas ícones) */
        .ods-icon {
            padding: 2px;
            background: transparent;
            border: none;
            border-radius: 4px;
        }

        .ods-icon:hover {
            background: #f1f3f5;
            transform: scale(1.1);
        }

        .ods-icon img {
            width: 28px;
            height: 28px;
            border-radius: 3px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #868e96;
            font-size: 0.85rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .docentes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="relatorio-docentes">
        <!--<h1>👨‍🏫 Corpo Docente - IGC USP</h1>-->

        <div class="docentes-grid">
            @foreach($docentes as $docente)
                <div class="docente-card">
                    
                    {{-- Cabeçalho: Foto + Depto + Nome + Email --}}
                    <div class="docente-header">
                        <div class="docente-foto">
                            @if($docente['foto_url'])
                                <img src="{{ $docente['foto_url'] }}" alt="{{ $docente['nome'] }}">
                            @else
                                <div class="sem-foto">📷</div>
                            @endif
                        </div>
                        <div class="docente-info">
                            <span class="docente-depto-badge">
                                {{ $docente['departamento_sigla'] }}
                            </span>
                            <div class="docente-nome">{{ $docente['nome'] }}</div>
                            
                            @if($docente['email'])
                                <div class="docente-email">
                                    <a href="mailto:{{ $docente['email'] }}">{{ $docente['email'] }}</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Links e ODS em linhas separadas --}}
                    @if(!empty($docente['links']) || !empty($docente['ods']))
                        <div class="docente-links-section">
                            
                            {{-- Linha 1: Links Acadêmicos --}}
                            @if(!empty($docente['links']))
                                <div class="docente-links-row">
                                    @foreach($docente['links'] as $link)
                                        <a href="{{ $link['url'] }}" target="_blank" class="docente-link" title="{{ $link['nome'] }}">
                                            @if(!empty($link['icone']))
                                                <img src="{{ $link['icone'] }}" alt="{{ $link['nome'] }}">
                                            @endif
                                            <!--<span>{{ $link['nome'] }}</span>-->
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Linha 2: ODS (apenas ícones com links) --}}
                            @if(!empty($docente['ods']))
                                <div class="docente-links-row">
                                    @foreach($docente['ods'] as $ods)
                                        <a href="{{ $ods['url'] }}" target="_blank" class="docente-link ods-icon" title="{{ $ods['nome'] }}">
                                            <img src="{{ $ods['img'] }}" alt="ODS {{ $ods['id'] }}">
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                    @endif

                </div>
            @endforeach
        </div>
        
        <p class="subtitle">
            Total de {{ $total }} docentes
        </p>

        <div class="footer">
            Última atualização: {{ date('d/m/Y \à\s H:i') }}<br>
            Gerado automaticamente pelo sistema SARaH
        </div>
    </div>
</body>
</html>