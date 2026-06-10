<?php

namespace App\Listeners;

use App\Models\LinkAcademico;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Uspdev\Replicado\Lattes;

class SyncUsuarioReplicado
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // 1. Só executa se o usuário tiver codpes e ainda não tiver links cadastrados no SARaH
        if (!$user->codpes || $user->links()->exists()) {
            return;
        }

        try {
            // 2. Busca o ID Lattes diretamente
            $lattesId = Lattes::id($user->codpes);

            // 3. Busca o ORCID diretamente
            $orcid = Lattes::retornarOrcidID($user->codpes);

            // 4. Salva o Lattes se encontrou
            if (!empty($lattesId)) {
                LinkAcademico::updateOrCreate(
                    ['user_id' => $user->id, 'plataforma' => 'lattes'],
                    ['identificador' => $lattesId]
                );
            }

            // 5. Salva o ORCID se encontrou
            if (!empty($orcid)) {
                // Se veio como URL (ex: https://orcid.org/0000-0000-0000-0000), extrai só o ID
                if (str_contains($orcid, 'orcid.org/')) {
                    $orcid = basename(parse_url($orcid, PHP_URL_PATH));
                }

                // Remove barras finais ou espaços extras
                $orcid = trim($orcid, '/ ');

                // Valida o formato antes de salvar (4 grupos de 4 caracteres separados por hífen)
                if (preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid)) {
                    LinkAcademico::updateOrCreate(
                        ['user_id' => $user->id, 'plataforma' => 'orcid'],
                        ['identificador' => $orcid]
                    );
                } else {
                    Log::warning("SARaH: ORCID inválido para {$user->codpes}: {$orcid}");
                }
            }

            Log::info("SARaH: Links acadêmicos sincronizados via Replicado para {$user->codpes}");

        } catch (\Exception $e) {
            // Falha silenciosa para não quebrar o fluxo de login do usuário
            // (Ex: usuário sem Lattes vinculado na USP, ou serviço do Replicado offline)
            Log::warning("SARaH: Falha ao sincronizar Replicado para {$user->codpes}. Erro: " . $e->getMessage());
        }
    }
}