<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OdsUsuario extends Model
{
    protected $table = 'ods_usuario';
    protected $fillable = ['user_id', 'ods_id'];
}