<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TutorEgibide extends Model {
    protected $table = 'tutores';

    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'ciudad',
        'user_id',
        'alias'
    ];

    /**
     * Usuario asociado al tutor
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Estancias asociadas al tutor
     */
    public function estancias(): HasMany {
        return $this->hasMany(Estancia::class, 'tutor_id');
    }

    /**
     * Alumnos con datos de la estancia (pivot)
     */
    public function alumnosConEstancia() {
        return $this->belongsToMany(
            Alumnos::class,
            'estancias',
            'tutor_id',
            'alumno_id'
        )->withPivot([
            'id',
            'puesto',
            'fecha_inicio',
            'fecha_fin',
            'horas_totales',
            'instructor_id',
            'empresa_id',
            'curso_id'
        ])->withTimestamps();
    }

    /**
     * Familias profesionales a los que pertenece este tutor
     */
    public function familias(): BelongsToMany {
        return $this->belongsToMany(
            FamiliaProfesional::class,
            'familia_tutor',
            'tutor_id',
            'familias_profesionales_id'
        )->withTimestamps();
    }
}
