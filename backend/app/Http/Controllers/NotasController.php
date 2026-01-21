<?php

namespace App\Http\Controllers;

use App\Services\CalcularNotasCompetenciasTecnicas;
use App\Services\CalcularNotasCompetenciasTransversales;

class NotasController extends Controller {
    public function obtenerNotasTecnicas($alumnoId, CalcularNotasCompetenciasTecnicas $calcularNotas) {
        $notas = $calcularNotas->calcularNotasTecnicas($alumnoId);

        return response()->json([
            'alumno_id' => $alumnoId,
            'notas_competenciasTec' => array_values($notas),
        ]);
    }

    public function obtenerNotasTransversales($alumnoId, CalcularNotasCompetenciasTransversales $calcularNotas) {
        $notas = $calcularNotas->calcularNotasTransversales($alumnoId);

        return response()->json([
            'estancia_id' => $notas['estancia_id'],
            'nota_media' => $notas['nota_media'],
        ]);
    }
}
