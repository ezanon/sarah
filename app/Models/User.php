<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use \Spatie\Permission\Traits\HasRoles;
    use \Uspdev\SenhaunicaSocialite\Traits\HasSenhaunica;

    protected $fillable = [
        'name',
        'email',
        'password',
        'codpes',
        'nivel_cnpq',
        'autoriza_foto_publica',
        'duplo_vinculo',
        'nomabvset',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'autoriza_foto_publica' => 'boolean',
    ];
    
    public function linksAcademicos()
    {
        return $this->hasMany(\App\Models\LinkAcademico::class);
    }
    
    
    public function equipamentosCriados()
    {
        return $this->hasMany(Equipamento::class);
    }

    public function equipamentosResponsavel()
    {
        return $this->belongsToMany(Equipamento::class, 'equipamento_responsavel')
            ->withTimestamps();
    }
    
    public function obterDadosPublicos()
    {
        $dados = [
            'foto_url' => null,
            'links' => [],
            'ods' => [],
        ];

        // Foto: só aparece se autorizou
        if ($this->autoriza_foto_publica) {
            $fotoPath = "fotos/{$this->codpes}.jpg";
            if (\Storage::disk('public')->exists($fotoPath)) {
                $timestamp = \Storage::disk('public')->lastModified($fotoPath);
                $dados['foto_url'] = asset("storage/{$fotoPath}?v={$timestamp}");
            } else {
                try {
                    $fotoBase64 = \Uspdev\Wsfoto::obter($this->codpes);
                    if ($fotoBase64) {
                        $dados['foto_url'] = 'data:image/png;base64,' . $fotoBase64;
                    }
                } catch (\Exception $e) {
                    // Sem foto disponível
                }
            }
        }

        // Links acadêmicos com ícones e URLs (ordenados por prioridade)
        $tiposConfig = config('links_academicos.tipos');
        
        // Define a ordem de exibição
        $prioridade = [
            'lattes' => 1,
            'orcid' => 2,
            'bv-fapesp' => 3,
            'google-scholar' => 4,
            'scopus' => 5,
            'researchid' => 6,
            'researchgate' => 7,
        ];

        $dados['links'] = $this->linksAcademicos
            ->sortBy(function($link) use ($prioridade) {
                // Se a plataforma estiver na lista de prioridade, usa o número. 
                // Se não (ex: plataforma nova), joga para o final (99).
                return $prioridade[$link->plataforma] ?? 99;
            })
            ->map(function($link) use ($tiposConfig) {
                $plataforma = $link->plataforma ?? 'link';
                $config = $tiposConfig[$plataforma] ?? null;
                
                // Monta a URL completa: base_url + identificador
                $urlCompleta = $config 
                    ? rtrim($config['base_url'], '/') . '/' . ltrim($link->identificador, '/')
                    : $link->identificador;
                
                return [
                    'nome' => $config['nome_exibicao'] ?? ucfirst($plataforma),
                    'url' => $urlCompleta,
                    'icone' => $config ? asset("images/{$config['icone']}") : null,
                ];
            })
            ->values() // Reindexa o array após a ordenação
            ->toArray();

        // ODS com imagens e links
        $odsIds = \App\Models\OdsUsuario::where('user_id', $this->id)->pluck('ods_id')->toArray();
        $odsList = \App\Http\Controllers\OdsController::ODS_LIST;
        
        $dados['ods'] = array_map(function($id) use ($odsList) {
            return [
                'id' => $id,
                'nome' => $odsList[$id]['nome'] ?? "ODS {$id}",
                'img' => asset("images/ods/SDG-" . str_pad($id, 2, '0', STR_PAD_LEFT) . ".jpg"),
                'url' => "https://brasil.un.org/pt-br/sdgs/{$id}",
            ];
        }, $odsIds);

        return $dados;
    }
    
}