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
        'moeda',
        'cod_processo_incorporacao',
        'foto',
        'ativo',
        'motivo_inativacao',
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
     * (Criador, Responsável OU detentor da permissão c_pesquisa)
     */
    public function podeEditar(User $user): bool
    {
        // 1. Se tiver a permissão c_pesquisa, pode editar TUDO
        if ($user->hasPermissionTo('c_pesquisa')) {
            return true;
        }

        // 2. Caso contrário, verifica se é o criador ou um dos responsáveis
        return $this->user_id === $user->id
            || $this->responsaveis()->where('user_id', $user->id)->exists();
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset("storage/{$this->foto}") : null;
    }
    
    public function getValorFormatadoAttribute(): string
    {
        if (!$this->valor) {
            return '-';
        }

        $simbolos = [
            'BRL' => 'R$',
            'USD' => 'US$',
            'EUR' => '€',
        ];

        $simbolo = $simbolos[$this->moeda] ?? 'R$';
        $valor = number_format($this->valor, 2, ',', '.');

        return "{$simbolo} {$valor}";
    }
    
}