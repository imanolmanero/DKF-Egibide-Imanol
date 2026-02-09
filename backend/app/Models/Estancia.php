<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\CuadernoPracticas;

class Estancia extends Model {
    protected $table = 'estancias';

    protected $fillable = [
        'puesto',
        'fecha_inicio',
        'fecha_fin',
        'horas_totales',
        'alumno_id',
        'tutor_id',
        'instructor_id',
        'empresa_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Get the alumno that owns this estancia
     */
    public function alumno(): BelongsTo {
        return $this->belongsTo(Alumnos::class,'alumno_id', 'id');
    }

    /**
     * Get the tutor (TutorEgibide) for this estancia
     */
    public function tutor(): BelongsTo {
        return $this->belongsTo(TutorEgibide::class, 'tutor_id', 'id');
    }

    /**
     * Get the instructor (TutorEmpresa) for this estancia
     */
    public function instructor(): BelongsTo {
        return $this->belongsTo(TutorEmpresa::class,'instructor_id', 'id');
    }

    /**
     * Get the empresa that owns this estancia
     */
    public function empresa(): BelongsTo {
        return $this->belongsTo(Empresas::class,'empresa_id');
    }

    /**
     * Get the curso for this estancia
     */
    public function curso(): BelongsTo {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Get all notas competencia tec for this estancia
     */
    public function notasCompetenciaTec(): HasMany {
        return $this->hasMany(NotaCompetenciaTec::class);
    }

    /**
     * Get all notas competencia trans for this estancia
     */
    public function notasCompetenciaTrans(): HasMany {
        return $this->hasMany(NotaCompetenciaTransversal::class);
    }

    /**
     * Get all seguimientos for this estancia
     */
    public function seguimientos(): HasMany {
        return $this->hasMany(Seguimiento::class);
    }

    /**
     * Get all horarios dia for this estancia
     */
    public function horariosDia(): HasMany {
        return $this->hasMany(HorarioDia::class);
    }

    /**
     * Get the cuaderno practicas for this estancia
     */
    public function cuadernoPracticas(): HasOne {
        return $this->hasOne(CuadernoPracticas::class,'estancia_id','id');
    }
}
