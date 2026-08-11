<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docentes - IGC USP</title>
    <style>
        
        body {
            margin: 0;
        }
        
        .relatorio-docentes {
            font-family: -apple-system, B linkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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

        .banner-topo {
            width: 100%;
            max-width: 1200px; /* Alinha perfeitamente com o container */
            display: block;
            margin: 0 auto;
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
            cursor: pointer;
        }

        .docente-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            border-color: #1a5490;
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

        /* Links acadêmicos (apenas ícones, sem texto) */
        .docente-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
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

        /* ===== NOVO: Estilos do Rodapé Personalizado ===== */
        .rodape-custom {
            background-color: #777;
            color: #fff;
            padding: 20px 0;
            margin-top: 40px;
            width: 100%;
        }

        .rodape-conteudo {
            max-width: 1200px; /* Alinha com o container principal */
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .rodape-logo {
            height: 50px;
            width: auto;
        }

        .rodape-texto {
            font-size: 0.9rem;
            margin: 0;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 100000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: fadeIn 0.2s ease;
            overflow-y: auto;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            border-radius: 12px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.3s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin: auto;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.9);
            cursor: pointer;
            font-size: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            z-index: 10;
        }

        .modal-close:hover {
            background: #fff;
            color: #c00;
        }

        .modal-header {
            display: flex;
            gap: 25px;
            padding: 30px;
            border-bottom: 1px solid #eee;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        }

        .modal-foto {
            flex-shrink: 0;
            width: 180px;
            height: 240px;
            border-radius: 8px;
            overflow: hidden;
            border: 3px solid #1a5490;
            background: #f5f5f5;
        }

        .modal-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-info {
            flex: 1;
            min-width: 0;
        }

        .modal-nome {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a5490;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .modal-depto {
            font-size: 1rem;
            color: #555;
            margin-bottom: 15px;
        }

        .modal-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }

        .modal-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #444;
        }

        .modal-meta-item strong {
            color: #1a5490;
            min-width: 110px;
        }

        .modal-meta-item a {
            color: #1a5490;
            text-decoration: none;
        }

        .modal-meta-item a:hover {
            text-decoration: underline;
        }

        .nivel-badge {
            display: inline-block;
            background: #ffd700;
            color: #333;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .duplo-badge {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-section {
            margin-bottom: 25px;
        }

        .modal-section:last-child {
            margin-bottom: 0;
        }

        .modal-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a5490;
            margin: 0 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #1a5490;
        }

        .modal-section-content {
            color: #444;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .linhas-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .linhas-list li {
            padding: 6px 0 6px 20px;
            position: relative;
        }

        .linhas-list li::before {
            content: "▸";
            position: absolute;
            left: 0;
            color: #1a5490;
            font-weight: bold;
        }

        .formacao-item {
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #1a5490;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .formacao-nivel {
            font-weight: 600;
            color: #1a5490;
        }

        .formacao-curso {
            color: #333;
            font-size: 0.95rem;
            margin-top: 4px;
        }

        .formacao-inst {
            color: #555;
            font-size: 0.9rem;
        }

        .formacao-ano {
            color: #888;
            font-size: 0.85rem;
        }

        .artigo-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            border-left: 3px solid #1a5490;
        }

        .artigo-titulo {
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .artigo-autores {
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .artigo-autores strong {
            color: #1a5490;
            background: #e8f0fe;
            padding: 1px 4px;
            border-radius: 3px;
        }

        .artigo-revista {
            color: #888;
            font-size: 0.85rem;
            font-style: italic;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: #888;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1a5490;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .lattes-update {
            font-size: 0.85rem;
            color: #888;
            font-style: italic;
            margin-top: 8px;
        }

        /* ===== RESPONSIVIDADE (MOBILE) ===== */
        @media (max-width: 768px) {
            .docentes-grid {
                grid-template-columns: 1fr;
            }

            .modal-overlay {
                align-items: flex-start;
                padding: 10px;
            }

            .modal-content {
                max-height: 95vh;
                margin: 10px auto;
            }

            .modal-content .modal-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 15px;
            }

            .modal-content .modal-foto {
                width: 120px;
                height: 160px;
            }

            .modal-content .modal-info {
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
            }

            .modal-content .modal-nome {
                font-size: 1.3rem;
                justify-content: center;
            }

            .modal-content .modal-depto {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
                margin-bottom: 10px;
            }

            .modal-content .modal-meta {
                align-items: center;
            }

            .modal-content .modal-meta-item {
                justify-content: center;
            }

            .modal-content .docente-links-row {
                justify-content: center;
            }

            .modal-content .modal-body {
                padding: 15px;
            }

            /* Ajuste responsivo do rodapé */
            .rodape-conteudo {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .modal-content .modal-foto {
                width: 100px;
                height: 135px;
            }

            .modal-content .modal-nome {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    
    {{-- 🖼️ BANNER NO TOPO (Fora do container, mas com a mesma largura máxima) --}}
    <img src="{{ asset('images/relatorios/docentes-banner.png') }}" alt="Banner Corpo Docente IGC USP" class="banner-topo">

    <div class="relatorio-docentes">
        <!--<h1>👨‍🏫 Corpo Docente - IGC USP</h1>-->

        <div class="docentes-grid">
            @foreach($docentes as $docente)
                <div class="docente-card" onclick="abrirModal('{{ $docente['codpes'] }}')">
                    
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
                                    <a href="mailto:{{ $docente['email'] }}" onclick="event.stopPropagation()">{{ $docente['email'] }}</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Links e ODS em linhas separadas --}}
                    @if(!empty($docente['links']) || !empty($docente['ods']))
                        <div class="docente-links-section">
                            
                            {{-- Linha 1: Links Acadêmicos (apenas ícones) --}}
                            @if(!empty($docente['links']))
                                <div class="docente-links-row">
                                    @foreach($docente['links'] as $link)
                                        <a href="{{ $link['url'] }}" target="_blank" class="docente-link" title="{{ $link['nome'] }}" onclick="event.stopPropagation()">
                                            @if(!empty($link['icone']))
                                                <img src="{{ $link['icone'] }}" alt="{{ $link['nome'] }}">
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Linha 2: ODS (apenas ícones com links) --}}
                            @if(!empty($docente['ods']))
                                <div class="docente-links-row">
                                    @foreach($docente['ods'] as $ods)
                                        <a href="{{ $ods['url'] }}" target="_blank" class="docente-link ods-icon" title="{{ $ods['nome'] }}" onclick="event.stopPropagation()">
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

    {{-- 🦶 RODAPÉ PERSONALIZADO COM FUNDO #777 --}}
    <footer class="rodape-custom">
        <div class="rodape-conteudo">
            <img src="{{ asset('images/relatorios/logo-igc-transparente-pequeno.png') }}" alt="Logotipo IGC USP" class="rodape-logo">
            <p class="rodape-texto">IGc/USP © 1999-{{ date('Y') }}. Todos os Direitos Reservados.</p>
        </div>
    </footer>

    <!-- Modal -->
    <div id="modalDocente" class="modal-overlay" onclick="fecharModalFora(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="fecharModal()" aria-label="Fechar">×</button>
            <div id="modalConteudo">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Carregando dados do docente...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let codpesAtual = null;

        function abrirModal(codpes) {
            codpesAtual = codpes;
            const modal = document.getElementById('modalDocente');
            const conteudo = document.getElementById('modalConteudo');
            
            // Mostra spinner
            conteudo.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Carregando dados do docente...</p>
                </div>
            `;
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Busca dados via API
            fetch(`{{ config('app.url') }}/api/docente/${codpes}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        conteudo.innerHTML = `<div class="modal-body"><p>Erro: ${data.error}</p></div>`;
                        return;
                    }
                    renderizarModal(data);
                })
                .catch(err => {
                    conteudo.innerHTML = `<div class="modal-body"><p>Erro ao carregar dados.</p></div>`;
                    console.error(err);
                });
        }

        function renderizarModal(data) {
            const conteudo = document.getElementById('modalConteudo');
            
            const fotoHtml = data.foto_url 
                ? `<img src="${data.foto_url}" alt="${data.nome}">`
                : `<div class="sem-foto">📷</div>`;
            
            const nivelHtml = data.nivel_cnpq 
                ? `<div class="modal-meta-item"><strong>Nível CNPq:</strong> <span class="nivel-badge">${data.nivel_cnpq}</span></div>` 
                : '';
            
            const duploHtml = data.duplo_vinculo 
                ? `<div class="modal-meta-item"><strong>Duplo vínculo:</strong> <span class="duplo-badge">${data.duplo_vinculo}</span></div>` 
                : '';
            
            const linksHtml = data.links && data.links.length > 0
                ? data.links.map(l => `
                    <a href="${l.url}" target="_blank" class="docente-link" title="${l.nome}" style="gap: 6px; padding: 6px 12px;">
                        ${l.icone ? `<img src="${l.icone}" alt="${l.nome}">` : ''}
                        <span>${l.nome}</span>
                    </a>
                `).join('')
                : '<em>Nenhum link cadastrado</em>';
            
            const odsHtml = data.ods && data.ods.length > 0
                ? data.ods.map(o => `<a href="${o.url}" target="_blank" class="docente-link ods-icon" title="${o.nome}"><img src="${o.img}"></a>`).join('')
                : '';

            const lattes = data.lattes || {};
            const linhasHtml = lattes.linhas_pesquisa && lattes.linhas_pesquisa.length > 0
                ? `<ul class="linhas-list">${lattes.linhas_pesquisa.map(l => `<li>${l}</li>`).join('')}</ul>`
                : '<em>Linhas de pesquisa não informadas</em>';
            
            const formacaoHtml = lattes.formacao && lattes.formacao.length > 0
                ? lattes.formacao.slice().reverse().map(f => `
                    <div class="formacao-item">
                        <div class="formacao-nivel">${f.nivel}</div>
                        ${f.curso ? `<div class="formacao-curso"><strong>Curso:</strong> ${f.curso}</div>` : ''}
                        ${f.instituicao ? `<div class="formacao-inst">${f.instituicao}</div>` : ''}
                        ${f.ano ? `<div class="formacao-ano">Ano de Conclusão: ${f.ano}</div>` : ''}
                    </div>
                `).join('')
                : '<em>Formação acadêmica não informada</em>';
            
            const artigosHtml = lattes.artigos && lattes.artigos.length > 0
                ? lattes.artigos.map(a => `
                    <div class="artigo-item">
                        ${a.titulo ? `<div class="artigo-titulo">${a.titulo}</div>` : ''}
                        ${a.autores ? `<div class="artigo-autores">${a.autores}</div>` : ''}
                        <div class="artigo-revista">
                            ${a.revista ? a.revista : ''}
                            ${a.volume ? 'v. ' + a.volume : ''}
                            ${a.pagina_inicial ? 'p. ' + a.pagina_inicial + (a.pagina_final ? '-' + a.pagina_final : '') : ''}
                            ${a.ano ? '• ' + a.ano : ''}
                        </div>
                    </div>
                `).join('')
                : '<em>Nenhum artigo encontrado</em>';

            const dataAtualizacao = lattes.data_atualizacao 
                ? `<div class="lattes-update">📅 Currículo Lattes atualizado em: ${lattes.data_atualizacao}</div>` 
                : '';
                
            const seniorBadge = data.eh_senior 
                ? `<span style="display: inline-block; background: #9b59b6; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; vertical-align: middle; margin-left: 10px;">Senior</span>` 
                : '';

            conteudo.innerHTML = `
                <div class="modal-header">
                    <div class="modal-foto">${fotoHtml}</div>
                    <div class="modal-info">
                        <h2 class="modal-nome">${data.nome} ${seniorBadge}</h2>
                        <div style="margin-bottom: 10px;">
                            <span class="docente-depto-badge" style="display: inline-block; background: #1a5490; color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                ${data.departamento.sigla}
                            </span>
                            <span style="color: #555; margin-left: 8px; font-size: 0.95rem;">
                                ${data.departamento.nome}
                            </span>
                        </div>
                        <div class="modal-meta">
                            ${data.email ? `<div class="modal-meta-item"><a href="mailto:${data.email}">${data.email}</a></div>` : ''}
                            ${nivelHtml}
                            ${duploHtml}
                        </div>
                        <div class="docente-links-row" style="margin-top: 10px;">
                            ${linksHtml}
                        </div>
                        ${odsHtml ? `
                            <div style="margin-top: 10px;">
                                <strong style="font-size: 0.9rem; color: #1a5490; display: block; margin-bottom: 6px;">ODS - Objetivos de Desenvolvimento Sustentável</strong>
                                <div class="docente-links-row">
                                    ${odsHtml}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="modal-body">
                    <!-- 1. Linhas de Pesquisa -->
                    <div class="modal-section">
                        <h3 class="modal-section-title">🔬 Linhas de Pesquisa</h3>
                        <div class="modal-section-content">${linhasHtml}</div>
                    </div>

                    <!-- 2. Resumo do Currículo Lattes -->
                    ${lattes.resumo ? `
                        <div class="modal-section">
                            <h3 class="modal-section-title">📝 Resumo do Currículo Lattes</h3>
                            <div class="modal-section-content">${lattes.resumo}</div>
                            ${lattes.data_atualizacao ? `<div class="lattes-update" style="font-size: 0.85rem; color: #888; font-style: italic; margin-top: 8px;">📅 Última atualização: ${lattes.data_atualizacao}</div>` : ''}
                        </div>
                    ` : ''}

                    <!-- 3. Formação Acadêmica -->
                    <div class="modal-section">
                        <h3 class="modal-section-title">🎓 Formação Acadêmica</h3>
                        <div class="modal-section-content">${formacaoHtml}</div>
                    </div>

                    <!-- 4. Últimos Artigos Publicados -->
                    <div class="modal-section">
                        <h3 class="modal-section-title"> Últimos Artigos Publicados</h3>
                        <div class="modal-section-content">${artigosHtml}</div>
                    </div>
                </div>
            `;
        }

        function fecharModal() {
            const modal = document.getElementById('modalDocente');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            codpesAtual = null;
        }

        function fecharModalFora(event) {
            if (event.target === event.currentTarget) {
                fecharModal();
            }
        }

        // Fecha com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && codpesAtual) {
                fecharModal();
            }
        });
    </script>
</body>
</html>