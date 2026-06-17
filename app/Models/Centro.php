<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centro extends Model
{
    protected $fillable = ['nome', 'sigla'];

    public function laboratorios()
    {
        return $this->hasMany(Laboratorio::class);
    }

    public function equipamentos()
    {
        return $this->hasManyThrough(Equipamento::class, Laboratorio::class);
    }
}