<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoSala extends Model {

    use HasFactory;

    protected $fillable = ['nome'];
    protected $table = 'tipos_sala';

    public function salas() {
        return $this->hasMany(Sala::class);
    }
}
