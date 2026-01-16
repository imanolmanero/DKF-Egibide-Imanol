<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguimientos extends Model
{
    protected $table = 'seguimientos';

    protected $primaryKey = 'id_seguimiento';

    protected $fillable = [
        'accion',
        'fecha',
        'descripcion',
        'via',
        'id_estancia',
    ];
}
