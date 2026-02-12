<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Estancia;

class Alumnos extends Model {
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'ciudad',
        'user_id',
        'grupo',
        'tutor_id',
        'dni',
        'matricula_id'
    ];

    /**
     * Get the user that owns this alumno
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all estancias for this alumno
     */
    public function estancias(): HasMany {
        return $this->hasMany(Estancia::class,'alumno_id','id');
    }

    /**
     * Get all notas asignatura for this alumno
     */
    public function notasAsignatura(): HasMany {
        return $this->hasMany(NotaAsignatura::class);
    }

    public function ciclo(): BelongsTo{
        return $this->belongsTo(Ciclos::class,'grupo','grupo');
    }

    public function tutor(): BelongsTo{
        return $this->belongsTo(TutorEgibide::class,'tutor_id','id');
    }
}
