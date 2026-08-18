<?php

// Caminho absoluto no servidor onde o SARAH salva o relatório
$caminhoArquivo = '/sites/sarah/public/relatorios/docentes.html';
// Define o cabeçalho como HTML
header('Content-Type: text/html; charset=utf-8');
if (file_exists($caminhoArquivo)) {
    // Exibe o conteúdo do HTML gerado pelo SARAH
    echo file_get_contents($caminhoArquivo);
} else {
    // Fallback caso o relatório ainda não tenha sido gerado
    echo '<!DOCTYPE html><html><body style="font-family: sans-serif; text-align: center; padding: 50px;">';
    echo '<h2>Relatório 
    em construção</h2>';
    echo '<p>O relatório de docentes está sendo gerado. Por favor, tente novamente em alguns 
    instantes.</p>';
    echo '</body></html>';
}
?>
