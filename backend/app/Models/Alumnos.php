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
        'curso_id',
        'tutor_id'
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

    public function curso(): BelongsTo{
        return $this->belongsTo(Curso::class,'curso_id','id');
    }
    public function tutor(): BelongsTo{
        return $this->belongsTo(TutorEgibide::class,'tutor_id','id');
    }
}
