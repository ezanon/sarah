<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Uspdev\Replicado\Lattes;

class TestarLattesReplicado extends Command
{
    protected $signature = 'lattes:array {funcao} {codpes}';
    protected $description = 'Testa e exibe o array completo do Lattes de um docente';

    public function handle()
    {
        $codpes = $this->argument('codpes');
        $funcao = $this->argument('funcao');
        
        $this->info("Buscando Lattes do codpes: {$codpes}");
        
        try {
            switch ($funcao){
                case 'completo': 
                    $arrayLattes = Lattes::obterArray($codpes);
                    $arquivo = public_path("lattes_{$codpes}.json");
                    break;
                case 
                    'projetos': 
                    $arrayLattes = Lattes::listarProjetosPesquisa($codpes, null, 'registros', 9999);
                    $arquivo = public_path("projetos_{$codpes}.json");
                    break;
                case 'artigosAno': 
                    $arrayLattes = Lattes::listarArtigos($codpes, null, 'periodo', date('Y')-1, date('Y')-1);
                    $arquivo = public_path("artigos_{$codpes}.json");
                    break;
            }
            
            file_put_contents($arquivo, json_encode($arrayLattes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $this->info("✅ Array salvo em: {$arquivo}");
            $this->info("🌐 Acesse via navegador: " . config('app.url') . "/lattes_{$codpes}.json");
            $this->info("📊 Estrutura principal:");
            
            // Corrige o formato para a tabela do terminal
            $chaves = array_map(fn($chave) => [$chave], array_keys($arrayLattes));
            $this->table(['Chave'], $chaves);
            
        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
        }
    }
    
}