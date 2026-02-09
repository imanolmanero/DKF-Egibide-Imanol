<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ciclos extends Model {

    protected $table = 'ciclos';
    protected $fillable = [
        'nombre',
        'familia_profesional_id'
    ];

    /**
     * Get the familia profesional that owns this ciclo
     */
    public function familiaProfesional(): BelongsTo {
        return $this->belongsTo(FamiliaProfesional::class);
    }

    /**
     * Get all cursos for this ciclo
     */
    public function cursos(): HasMany {
        return $this->hasMany(Curso::class, 'ciclo_id');
    }

    /**
     * Get all asignaturas for this ciclo
     */
    public function asignaturas(): HasMany {
        return $this->hasMany(Asignatura::class, 'ciclo_id');
    }

    /**
     * Get all competencias tec for this ciclo
     */
    public function competenciasTec(): HasMany {
        return $this->hasMany(CompetenciaTec::class);
    }

    public function tutores(){
        return $this->belongsToMany(TutorEgibide::class,'ciclo_tutor','curso_id','tutor_id');
    }

    public function alumnos(){
        return $this->hasMany(Alumnos::class,'curso_id','id');
    }
}
