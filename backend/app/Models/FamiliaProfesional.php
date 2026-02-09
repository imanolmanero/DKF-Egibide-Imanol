<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamiliaProfesional extends Model {
    protected $fillable = [
        'nombre',
        'codigo_familia',
        'centro_id'
    ];

    protected $table = 'familias_profesionales';

    /**
     * Get the centro that owns this familia profesional
     */
    public function centro(): BelongsToMany {
        return $this->belongsToMany(Centro::class, 'centros_familias', 'centro_id', 'familia_id');
    }

    /**
     * Get all ciclos for this familia profesional
     */
    public function ciclos(): HasMany {
        return $this->hasMany(Ciclos::class);
    }

    /**
     * Get all competencias transversales for this familia profesional
     */
    public function competenciasTransversales(): HasMany {
        return $this->hasMany(CompetenciaTransversal::class);
    }
}
