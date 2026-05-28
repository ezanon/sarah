<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Andar extends Model {

    use HasFactory;

    protected $table = 'andares';
    protected $fillable = ['numero'];

    public function salas() {
        return $this->hasMany(Sala::class);
    }
}
