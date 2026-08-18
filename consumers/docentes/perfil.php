<?php
// =============================================================================
// 1. LÓGICA DE BACKEND (Captura, Validação e Requisição à API)
// =============================================================================

$username = $_GET['u'] ?? '';

// Validação de segurança (apenas letras, números, ponto, hífen e underscore)
if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
    http_response_code(400);
    die('<h1>URL inválida</h1><p>O nome de usuário fornecido não é válido.</p>');
}

// URL da API no SARAH
$apiUrl = "https://sarah.igc.usp.br/api/docente/username/" . urlencode($username);

// Configuração e execução do cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Tratamento de erros de conexão
if ($httpCode !== 200 || !$response) {
    http_response_code(502); // Bad Gateway
    die("<h1>Erro de Conexão</h1><p>Não foi possível comunicar com o servidor de dados no momento.</p>");
}

// Decodificação e validação do JSON
$data = json_decode($response, true);

if (!$data || isset($data['error'])) {
    http_response_code($httpCode === 404 ? 404 : 500);
    $errorMsg = $data['error'] ?? 'Erro desconhecido na comunicação com o servidor.';
    die("<h1>Erro ao carregar dados</h1><p>" . htmlspecialchars($errorMsg) . "</p>");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['nome']) ?> - IGC USP</title>
    <style>
        :root {
            --primary: #1a5490;
            --bg: #f8f9fa;
            --text: #333;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        /* ALTERADO: max-width de 900px para 1200px para alinhar com o banner */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Banner e Rodapé */
        .banner-topo {
            width: 100%;
            max-width: 1200px;
            display: block;
            margin: 0 auto;
        }

        .rodape-custom {
            background-color: #777;
            color: #fff;
            padding: 20px 0;
            margin-top: 50px;
        }

        .rodape-conteudo {
            max-width: 1200px;
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
        }

        /* Card Principal */
        .docente-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 40px;
            margin: 30px 0;
        }

        .docente-header {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .docente-foto {
            flex-shrink: 0;
            width: 180px;
            height: 240px;
            border-radius: 8px;
            overflow: hidden;
            border: 3px solid var(--primary);
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #adb5bd;
        }

        .docente-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .docente-info h1 {
            color: var(--primary);
            margin: 0 0 10px 0;
            font-size: 2rem;
        }

        .depto-badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .meta-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-cnpq { background: #ffd700; color: #333; }
        .badge-senior { background: #9b59b6; color: white; }
        .badge-duplo { background: #e74c3c; color: white; }

        /* Seções de Conteúdo */
        .section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .section-title {
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
        }

        /* Listas e Links */
        .links-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .link-icon {
            display: inline-flex;
            align-items: center;
            padding: 6px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            text-decoration: none;
            transition: transform 0.2s;
        }

        .link-icon:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
        }

        .link-icon img {
            width: 20px;
            height: 20px;
            object-fit: contain;
            margin-right: 6px;
        }

        .link-icon span {
            font-size: 0.85rem;
            color: #333;
            padding-right: 8px;
        }

        .formacao-item {
            background: #fff;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            border-left: 3px solid var(--primary);
        }

        .formacao-nivel {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.95rem;
        }

        .artigo-item {
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 12px;
            border-left: 3px solid var(--primary);
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
            color: var(--primary);
            background: #e8f0fe;
            padding: 1px 4px;
            border-radius: 3px;
        }

        .artigo-revista {
            color: #888;
            font-size: 0.85rem;
            font-style: italic;
        }

        .btn-voltar {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .btn-voltar:hover {
            background: #5a6268;
        }

        /* Responsividade e Impressão */
        @media (max-width: 600px) {
            .docente-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .meta-badges {
                justify-content: center;
            }
        }

        @media print {
            .btn-voltar, .rodape-custom {
                display: none !important;
            }
            body {
                background: white;
            }
            .docente-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            .section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Banner -->
    <img src="https://sarah.igc.usp.br/images/relatorios/docentes-banner.png" alt="Banner IGC USP" class="banner-topo">

    <div class="container">
        <a href="https://docentes.igc.usp.br/" class="btn-voltar">← Voltar para a lista de docentes</a>

        <div class="docente-card">
            <!-- Cabeçalho do Docente -->
            <div class="docente-header">
                <div class="docente-foto">
                    <?php if (!empty($data['foto_url'])): ?>
                        <img src="<?= htmlspecialchars($data['foto_url']) ?>" alt="Foto de <?= htmlspecialchars($data['nome']) ?>">
                    <?php else: ?>
                        📷
                    <?php endif; ?>
                </div>
                
                <div class="docente-info">
                    <h1><?= htmlspecialchars($data['nome']) ?></h1>
                    
                    <?php if ($data['eh_senior']): ?>
                        <p><span class="badge badge-senior">Senior</span></p>
                    <?php endif; ?>
                        
                    <span class="depto-badge"><?= htmlspecialchars($data['departamento']['sigla']) ?></span>
                    <span style="color: #555; margin: 5px 0 15px 10px;"><?= htmlspecialchars($data['departamento']['nome']) ?></span>

                    <?php if (!empty($data['email'])): ?>
                        <p>
                            <a href="mailto:<?= htmlspecialchars($data['email']) ?>" style="color: var(--primary); text-decoration: none;">
                                <?= htmlspecialchars($data['email']) ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <div class="meta-badges">
                        <?php if (!empty($data['nivel_cnpq'])): ?>
                        <span style="color: #555; margin: 0;">Nível CNPq: </span>
                        <span class="badge badge-cnpq"><?= htmlspecialchars($data['nivel_cnpq']) ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($data['duplo_vinculo'])): ?>
                            <span class="badge badge-duplo">Duplo Vínculo: <?= htmlspecialchars($data['duplo_vinculo']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Links Acadêmicos e ODS -->
            <?php if (!empty($data['links']) || !empty($data['ods'])): ?>
                <div class="section" style="border-left: none; background: transparent; padding: 0;">
                    
                    <!-- Links Acadêmicos -->
                    <?php if (!empty($data['links'])): ?>
                        <h4 style="font-size: 1rem; color: #333; font-weight: 600; margin-bottom: 12px;">
                            Links Acadêmicos
                        </h4>
                        <div class="links-row">
                            <?php foreach ($data['links'] as $link): ?>
                                <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="link-icon" title="<?= htmlspecialchars($link['nome']) ?>">
                                    <img src="<?= htmlspecialchars($link['icone']) ?>" alt="<?= htmlspecialchars($link['nome']) ?>">
                                    <span><?= htmlspecialchars($link['nome']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- ODS (Com título e ícones maiores) -->
                    <?php if (!empty($data['ods'])): ?>
                        <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                            <h4 style="font-size: 1rem; color: #333; font-weight: 600; margin-bottom: 12px;">
                                ODS - Objetivos de Desenvolvimento Sustentável
                            </h4>
                            <div class="links-row">
                                <?php foreach ($data['ods'] as $ods): ?>
                                    <a href="<?= htmlspecialchars($ods['url']) ?>" target="_blank" class="link-icon" title="<?= htmlspecialchars($ods['nome']) ?>" style="border: none; background: transparent; padding: 0; transition: transform 0.2s;">
                                        <img src="<?= htmlspecialchars($ods['img']) ?>" alt="ODS <?= htmlspecialchars($ods['id']) ?>" style="width: 36px; height: 36px; border-radius: 4px;">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

            <!-- Linhas de Pesquisa -->
            <?php if (!empty($data['lattes']['linhas_pesquisa'])): ?>
                <div class="section">
                    <h3 class="section-title">🔬 Linhas de Pesquisa</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($data['lattes']['linhas_pesquisa'] as $linha): ?>
                            <li style="margin-bottom: 8px;"><?= htmlspecialchars($linha) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Resumo Lattes -->
            <?php if (!empty($data['lattes']['resumo'])): ?>
                <div class="section">
                    <h3 class="section-title">📝 Resumo do Currículo Lattes</h3>
                    <p style="margin: 0; text-align: justify;"><?= nl2br(htmlspecialchars($data['lattes']['resumo'])) ?></p>
                    <?php if (!empty($data['lattes']['data_atualizacao'])): ?>
                        <p style="font-size: 0.85rem; color: #888; font-style: italic; margin-top: 10px;">
                            📅 Última atualização: <?= htmlspecialchars($data['lattes']['data_atualizacao']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Formação Acadêmica -->
            <?php if (!empty($data['lattes']['formacao'])): ?>
                <div class="section">
                    <h3 class="section-title">🎓 Formação Acadêmica</h3>
                    <?php foreach ($data['lattes']['formacao'] as $form): ?>
                        <div class="formacao-item">
                            <div class="formacao-nivel"><?= htmlspecialchars($form['nivel']) ?></div>
                            <?php if (!empty($form['curso'])): ?>
                                <div style="color: #333; font-size: 0.95rem;"><strong>Curso:</strong> <?= htmlspecialchars($form['curso']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($form['instituicao'])): ?>
                                <div style="color: #555; font-size: 0.9rem;"><?= htmlspecialchars($form['instituicao']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($form['ano'])): ?>
                                <div style="color: #888; font-size: 0.85rem;">Ano de Conclusão: <?= htmlspecialchars($form['ano']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Artigos -->
            <?php if (!empty($data['lattes']['artigos'])): ?>
                <div class="section">
                    <h3 class="section-title">📄 Últimos Artigos Publicados</h3>
                    <?php foreach ($data['lattes']['artigos'] as $artigo): ?>
                        <div class="artigo-item">
                            <?php if (!empty($artigo['titulo'])): ?>
                                <div class="artigo-titulo"><?= htmlspecialchars($artigo['titulo']) ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($artigo['autores'])): ?>
                                <div class="artigo-autores"><?= $artigo['autores'] ?></div>
                            <?php endif; ?>
                            
                            <div class="artigo-revista">
                                <?= htmlspecialchars($artigo['revista']) ?>
                                <?php if (!empty($artigo['volume'])): ?> v. <?= htmlspecialchars($artigo['volume']) ?><?php endif; ?>
                                <?php if (!empty($artigo['pagina_inicial'])): ?> p. <?= htmlspecialchars($artigo['pagina_inicial']) ?><?php if (!empty($artigo['pagina_final'])): ?>-<?= htmlspecialchars($artigo['pagina_final']) ?><?php endif; ?><?php endif; ?>
                                <?php if (!empty($artigo['ano'])): ?> • <?= htmlspecialchars($artigo['ano']) ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Rodapé -->
    <footer class="rodape-custom">
        <div class="rodape-conteudo">
            <img src="https://sarah.igc.usp.br/images/relatorios/logo-igc-transparente-pequeno.png" alt="Logotipo IGC USP" class="rodape-logo">
            <p style="font-size: 0.9rem; margin: 0;">IGc/USP © 1999-<?= date('Y') ?>. Todos os Direitos Reservados.</p>
        </div>
    </footer>

</body>
</html>