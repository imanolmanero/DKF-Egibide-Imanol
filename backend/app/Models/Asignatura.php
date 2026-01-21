<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignatura extends Model {
  protected $table = 'asignaturas';

  protected $fillable = [
    'codigo_asignatura',
    'nombre_asignatura',
    'ciclo_id',
  ];

  /**
   * Get the ciclo that owns this asignatura
   */
  public function ciclo(): BelongsTo {
    return $this->belongsTo(Ciclos::class, 'ciclo_id');
  }

  /**
   * Get all resultados aprendizaje for this asignatura
   */
  public function resultadosAprendizaje(): HasMany {
    return $this->hasMany(ResultadoAprendizaje::class);
  }

  /**
   * Get all notas for this asignatura
   */
  public function notasAsignatura(): HasMany {
    return $this->hasMany(NotaAsignatura::class);
  }
}
