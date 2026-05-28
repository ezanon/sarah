<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bloco extends Model {

    use HasFactory;

    protected $fillable = ['nome'];
    protected $table = 'blocos';

    public function salas() {
        return $this->hasMany(Sala::class);
    }
}
