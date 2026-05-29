<?php
// app/Models/LinkAcademico.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkAcademico extends Model
{
    protected $fillable = ['user_id', 'plataforma', 'identificador'];
    protected $table = 'links_academicos';

    // Mapeamento: plataforma → template de URL
    public const URL_TEMPLATES = [
        'lattes'        => 'http://lattes.cnpq.br/{id}',
        'orcid'         => 'https://orcid.org/{id}',
        'bv_fapesp'     => 'https://bv.fapesp.br/pesquisador/{id}',
        'google_scholar'=> 'https://scholar.google.com/citations?user={id}',
        'scopus'        => 'https://www.scopus.com/authid/detail.uri?authorId={id}',
        'researcher_id' => 'https://www.webofscience.com/wos/author/record/{id}',
        'researchgate'  => 'https://www.researchgate.net/profile/{id}',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Gera a URL completa a partir do identificador
    public function getUrlAttribute(): string
    {
        $template = self::URL_TEMPLATES[$this->plataforma] ?? '#';
        return str_replace('{id}', urlencode($this->identificador), $template);
    }

    // Lista amigável para o frontend
    public static function getPlataformas(): array
    {
        return [
            'lattes'         => ['nome' => 'Lattes', 'placeholder' => 'Ex: 1234567890123456'],
            'orcid'          => ['nome' => 'ORCID', 'placeholder' => 'Ex: 0000-0002-1234-5678'],
            'bv_fapesp'      => ['nome' => 'BV FAPESP', 'placeholder' => 'Ex: 012345'],
            'google_scholar' => ['nome' => 'Google Scholar', 'placeholder' => 'Ex: abcDEF123'],
            'scopus'         => ['nome' => 'Scopus', 'placeholder' => 'Ex: 12345678900'],
            'researcher_id'  => ['nome' => 'ResearcherID (WoS)', 'placeholder' => 'Ex: K-5073-2015'],
            'researchgate'   => ['nome' => 'ResearchGate', 'placeholder' => 'Ex: Joao_Silva_3'],
        ];
    }
}