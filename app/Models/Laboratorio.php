<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    protected $fillable = ['centro_id', 'nome', 'sigla'];

    public function centro()
    {
        return $this->belongsTo(Centro::class);
    }

    public function equipamentos()
    {
        return $this->hasMany(Equipamento::class);
    }
}