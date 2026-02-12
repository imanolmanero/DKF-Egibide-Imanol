<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TutorEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empresas extends Model {

    use HasFactory;

    protected $fillable = [
        'nombre',
        'cif',
        'telefono',
        'email',
        'direccion'
    ];

    /**
     * Get all instructores directamente asignados a esta empresa
     * CORREGIDO: Ahora usa hasMany en lugar de belongsToMany
     */
    public function instructores(): HasMany
    {
        return $this->hasMany(TutorEmpresa::class, 'empresa_id', 'id');
    }

    /**
     * Get all estancias for this empresa
     */
    public function estancias(): HasMany {
        return $this->hasMany(Estancia::class,'empresa_id', 'id');  
    }
}