<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sala extends Model {

    protected $fillable = ['user_id', 'tipo_sala_id', 'bloco_id', 'andar_id', 'numero', 'descricao'];
    protected $table = 'salas';

    public function user(): BelongsTo {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function tipo(): BelongsTo {
        return $this->belongsTo(TipoSala::class, 'tipo_sala_id');
    }

    public function bloco(): BelongsTo {
        return $this->belongsTo(Bloco::class);
    }

    public function andar(): BelongsTo {
        return $this->belongsTo(Andar::class);
    }
}
