<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrega extends Model
{
    use HasFactory;

    protected $table = 'entregas';

    protected $fillable = [
        'archivo',
        'fecha',
        'cuaderno_practicas_id',
        'entrega_cuaderno_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function cuadernoPracticas(): BelongsTo
    {
        return $this->belongsTo(CuadernoPracticas::class, 'cuaderno_practicas_id');
    }
}
