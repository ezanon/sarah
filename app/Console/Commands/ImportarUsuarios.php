<?php

namespace App\Console\Commands;

use App\Models\LinkAcademico;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Uspdev\Replicado\Lattes;
use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Pesquisa;
use Uspdev\Replicado\Posgraduacao;

class ImportarUsuarios extends Command
{
    protected $signature = 'sarah:importar-usuarios';
    protected $description = 'Importa docentes, pós-docs, colaboradores e pós-graduandos via Replicado (USP)';

    // Totais consolidados
    private int $totalCriados = 0;
    private int $totalAtualizados = 0;
    private int $totalErros = 0;

    // Lista detalhada de erros
    private array $errosDetalhes = [];

    public function handle(): int
    {
        $this->info('🚀 Iniciando importação de usuários do Replicado para o SARaH...');
        $this->newLine();

        // Define as fontes a importar
        $fontes = [
            [
                'nome'    => 'Docentes',
                'metodo'  => fn() => Pessoa::listarDocentes(null, 'A,P'),
                'icone'   => '👨‍🏫',
            ],
            [
                'nome'    => 'Pós-doutorandos',
                'metodo'  => fn() => Pesquisa::listarPesquisaPosDoutorandos(),
                'icone'   => '',
            ],
            [
                'nome'    => 'Colaboradores ativos',
                'metodo'  => fn() => Pesquisa::listarPesquisadoresColaboradoresAtivos(),
                'icone'   => '🤝',
            ],
            [
                'nome'    => 'Pós-graduandos ativos',
                'metodo'  => fn() => Posgraduacao::ativos(44),
                'icone'   => '',
            ],
        ];

        foreach ($fontes as $fonte) {
            $this->processarFonte($fonte['nome'], $fonte['metodo'], $fonte['icone']);
        }

        // Resumo final consolidado
        $this->newLine();
        $this->info('🎉 Importação geral concluída!');
        $this->table(
            ['Status', 'Quantidade'],
            [
                ['Criados', $this->totalCriados],
                ['Atualizados', $this->totalAtualizados],
                ['Erros', $this->totalErros],
            ]
        );

        // Exibe detalhes dos erros em formato simples (linha a linha)
        if (!empty($this->errosDetalhes)) {
            $this->newLine();
            $this->error('⚠️ Usuários que apresentaram erro durante a importação (Codpes, Nome, Fonte, Erro):');
            foreach ($this->errosDetalhes as $erro) {
                $this->line("  - {$erro[0]}, {$erro[1]}, {$erro[2]}, {$erro[3]}");
            }
        }

        return 0;
    }

    /**
     * Processa uma fonte específica do Replicado
     */
    private function processarFonte(string $nome, callable $metodo, string $icone): void
    {
        $this->info("{$icone} Buscando {$nome} no Replicado...");

        try {
            $lista = $metodo();
        } catch (\Exception $e) {
            $this->error("❌ Falha ao buscar {$nome}: " . $e->getMessage());
            $this->newLine();
            return;
        }

        if (empty($lista)) {
            $this->warn("⚠️ Nenhum {$nome} encontrado.");
            $this->newLine();
            return;
        }

        $this->info("   ↳ " . count($lista) . " registros encontrados. Processando...");

        $bar = $this->output->createProgressBar(count($lista));
        $bar->start();

        $criados = 0;
        $atualizados = 0;
        $erros = 0;

        foreach ($lista as $item) {
            $codpes = $item['codpes'] ?? null;
            
            //  Ajuste: verifica múltiplos nomes de campo para o nome do usuário
            $nompes = $item['nompes'] 
                ?? $item['nome_aluno']   // Pós-doutorandos
                ?? $item['pesquisador']  // Colaboradores
                ?? null;

            if (!$codpes || !$nompes) {
                $bar->advance();
                continue;
            }

            // 📧 Lógica robusta para buscar o email
            $email = null;
            
            // 1. Tenta o email USP oficial (ativo)
            try {
                $email = Pessoa::retornarEmailUsp($codpes);
            } catch (\Exception $e) {
                // Falhou, tenta o próximo
            }

            // 2. Se não achou, tenta o método email() genérico
            if (empty($email)) {
                try {
                    $email = Pessoa::email($codpes);
                } catch (\Exception $e) {
                    // Falhou, tenta o próximo
                }
            }

            // 3. Fallback final se nenhum método retornar email
            if (empty($email)) {
                $email = "{$codpes}@usp.br"; 
            }

            try {
                $user = User::updateOrCreate(
                    ['codpes' => $codpes],
                    [
                        'name'     => $nompes,
                        'email'    => $email,
                        'password' => Hash::make(Str::random(24)),
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $criados++;
                } else {
                    $atualizados++;
                }

                // Sincroniza links apenas se for novo ou ainda não tiver
                if ($user->wasRecentlyCreated || !$user->links()->exists()) {
                    $this->sincronizarLinks($user, $codpes);
                }

            } catch (\Exception $e) {
                $erros++;
                // Registra o erro detalhado
                $this->errosDetalhes[] = [
                    $codpes,
                    $nompes,
                    $nome,
                    $e->getMessage(),
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✅ {$nome}: {$criados} criados, {$atualizados} atualizados, {$erros} erros");
        $this->newLine();

        // Acumula nos totais gerais
        $this->totalCriados    += $criados;
        $this->totalAtualizados += $atualizados;
        $this->totalErros      += $erros;
    }

    /**
     * Busca e salva Lattes e ORCID no banco do SARaH
     */
    private function sincronizarLinks(User $user, string $codpes): void
    {
        try {
            $lattesId = Lattes::id($codpes);
            $orcid    = Lattes::retornarOrcidID($codpes);

            if (!empty($lattesId)) {
                LinkAcademico::updateOrCreate(
                    ['user_id' => $user->id, 'plataforma' => 'lattes'],
                    ['identificador' => $lattesId]
                );
            }

            if (!empty($orcid)) {
                if (str_contains($orcid, 'orcid.org/')) {
                    $orcid = basename(parse_url($orcid, PHP_URL_PATH));
                }
                $orcid = trim($orcid, '/ ');

                if (preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid)) {
                    LinkAcademico::updateOrCreate(
                        ['user_id' => $user->id, 'plataforma' => 'orcid'],
                        ['identificador' => $orcid]
                    );
                }
            }
        } catch (\Exception $e) {
            // Silencioso para não travar o loop
        }
    }
}