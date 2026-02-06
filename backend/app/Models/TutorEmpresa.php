<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorEmpresa extends Model {
    protected $table = 'instructores';

    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'ciudad',
        'empresa_id',
        'user_id',
        'alias'
    ];

    public function empresa() {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }

    public function usuario() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estancias() {
        return $this->hasMany(Estancia::class,'instructor_id', 'id');
    }

    public function alumnos() {
        return $this->hasManyThrough(
            Alumnos::class,
            Estancia::class,
            'instructor_id',
            'id',
            'id',
            'alumno_id'
        );
    }

    public function alumnosConEstancia() {
        return $this->belongsToMany(
            Alumnos::class,
            'estancias',
            'instructor_id',
            'alumno_id'
        )->withPivot([
            'id',
            'puesto',
            'fecha_inicio',
            'fecha_fin',
            'horas_totales',
            'tutor_id',
            'empresa_id',
            'curso_id'
        ])->withTimestamps();
    }
}
