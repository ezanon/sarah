<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatorioDiretoria extends Model
{
    protected $table = 'relatorios_diretoria';

    protected $fillable = [
        'departamento',
        'ano',
        'caminho_arquivo',
        'gerado_em',
        'user_id',
    ];

    protected $casts = [
        'gerado_em' => 'datetime',
    ];

    // Relação com o usuário que gerou
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Retorna a URL pública do relatório
    public function getUrlAttribute()
    {
        return asset($this->caminho_arquivo);
    }
}