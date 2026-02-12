<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaCuaderno extends Model
{
    protected $table = 'entregas_cuaderno';

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_limite',
        'ciclo_id',
        'tutor_id',
    ];

    public function ciclo(): BelongsTo {
        return $this->belongsTo(Ciclos::class, 'ciclo_id');
    }

    public function tutor(): BelongsTo {
        return $this->belongsTo(TutorEgibide::class, 'tutor_id');
    }
}
