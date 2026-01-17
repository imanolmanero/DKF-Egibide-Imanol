<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Centro extends Model {
  protected $fillable = [
    'nombre',
    'calle',
  ];

  /**
   * Get all familia profesional for the centro
   */
  public function familiasProfesionales(): BelongsToMany {
    return $this->belongsToMany(FamiliaProfesional::class,'centros_familias','centro_id','familia_id');
  }
}
