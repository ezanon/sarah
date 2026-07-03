<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LinkAcademico;

class ImportarDocentesCSV extends Command
{
    protected $signature = 'importar:linksdocentes {arquivo}';
    protected $description = 'Importa dados de docentes de um arquivo CSV';

    private $plataformas = [
        'lattes',
        'orcid',
        'google-scholar',
        'scopus',
        'researchid',
        'researchgate',
        'bv-fapesp',
    ];

    public function handle()
    {
        $arquivo = $this->argument('arquivo');
        
        if (!file_exists($arquivo)) {
            $this->error("Arquivo não encontrado: {$arquivo}");
            return 1;
        }

        $this->info("📂 Lendo arquivo: {$arquivo}");
        
        $linhas = array_map('str_getcsv', file($arquivo));
        $headers = array_shift($linhas);
        $headers = array_map('trim', $headers);
        
        $this->info("📊 Encontradas " . count($linhas) . " linhas para processar");
        
        $bar = $this->output->createProgressBar(count($linhas));
        $bar->start();
        
        $criados = 0;
        $atualizados = 0;
        $linksCriados = 0;
        
        foreach ($linhas as $linha) {
            $dados = array_combine($headers, $linha);
            $codpes = trim($dados['codpes'] ?? '');
            
            if (empty($codpes)) {
                $bar->advance();
                continue;
            }
            
            // Busca ou cria usuário
            $user = User::firstOrCreate(
                ['codpes' => $codpes],
                [
                    'name' => trim($dados['nompes'] ?? ''),
                    'email' => trim($dados['email'] ?? ''),
                ]
            );
            
            if ($user->wasRecentlyCreated) {
                $criados++;
            } else {
                $atualizados++;
            }
            
            // Atualiza campos se estiverem vazios
            $atualizacoes = [];
            
            if (empty($user->name) && !empty($dados['nompes'])) {
                $atualizacoes['name'] = trim($dados['nompes']);
            }
            
            if (empty($user->email) && !empty($dados['email'])) {
                $atualizacoes['email'] = trim($dados['email']);
            }
            
            if (empty($user->nivel_cnpq) && !empty($dados['nivel_cnpq'])) {
                $atualizacoes['nivel_cnpq'] = trim($dados['nivel_cnpq']);
            }
            
            // Atualiza duplo vínculo (se houver)
            if (!empty($dados['duplo_vinculo'])) {
                $atualizacoes['duplo_vinculo'] = trim($dados['duplo_vinculo']);
            }
            
            // Atualiza código do departamento (salva o código, não a sigla)
            if (!empty($dados['nomabvset'])) {
                $atualizacoes['nomabvset'] = trim($dados['nomabvset']);
            }
            
            if (!empty($atualizacoes)) {
                $user->update($atualizacoes);
            }
            
            // Processa links acadêmicos
            foreach ($this->plataformas as $plataforma) {
                $identificador = trim($dados[$plataforma] ?? '');
                
                if (empty($identificador)) {
                    continue;
                }
                
                // Verifica se já existe
                $existe = LinkAcademico::where('user_id', $user->id)
                    ->where('plataforma', $plataforma)
                    ->exists();
                
                if (!$existe) {
                    LinkAcademico::create([
                        'user_id' => $user->id,
                        'plataforma' => $plataforma,
                        'identificador' => $identificador,
                    ]);
                    $linksCriados++;
                }
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Importação concluída!");
        $this->line("  • Usuários criados: {$criados}");
        $this->line("  • Usuários atualizados: {$atualizados}");
        $this->line("  • Links acadêmicos criados: {$linksCriados}");
        
        return 0;
    }
}