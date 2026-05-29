<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Veiculo extends Model
{
    protected $fillable = ['user_id', 'placa', 'tipo', 'marca', 'modelo', 'cor'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}