<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Uspdev\Replicado\Lattes;

class TestarReplicado extends Command
{
    protected $signature = 'lattes:array {codpes}';
    protected $description = 'Testa e exibe o array completo do Lattes de um docente';

    public function handle()
    {
        $codpes = $this->argument('codpes');
        
        $this->info("Buscando Lattes do codpes: {$codpes}");
        
        try {
            $arrayLattes = Lattes::listarProjetosPesquisa($codpes);
            //$arrayLattes = Lattes::obterArray($codpes);
            
            $arquivo = public_path("lattes_{$codpes}.json");
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