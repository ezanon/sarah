<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipamento extends Model
{
    protected $fillable = [
        'laboratorio_id',
        'user_id',
        'nome',
        'marca',
        'modelo',
        'ano_aquisicao',
        'ano_incorporacao',
        'financiamento',
        'cod_processo_convenio',
        'patrimonio',
        'valor',
        'cod_processo_incorporacao',
        'foto',
        'ativo'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'ano_aquisicao' => 'integer',
        'ano_incorporacao' => 'integer',
    ];

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function centro()
    {
        return $this->laboratorio->centro ?? null;
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responsaveis()
    {
        return $this->belongsToMany(User::class, 'equipamento_responsavel')
            ->withTimestamps();
    }

    /**
     * Verifica se o usuário pode editar este equipamento
     * (criador ou responsável)
     */
    public function podeEditar(User $user): bool
    {
        return $this->user_id === $user->id 
            || $this->responsaveis()->where('user_id', $user->id)->exists();
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset("storage/{$this->foto}") : null;
    }
}