<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipamento extends Model
{
    protected $fillable = [
        'laboratorio_id',
        'user_id',
        'nome',
        'tipo',
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

    // Constantes para os tipos de equipamento
    public const TIPOS = [
        'grande_porte' => 'Grande Porte',
        'periferico'   => 'Periférico',
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

    public function podeEditar(User $user): bool
    {
        if ($user->hasPermissionTo('c_pesquisa')) {
            return true;
        }

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

    public function getTipoFormatadoAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }
}