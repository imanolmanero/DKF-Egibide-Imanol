<?php

namespace App\Services;

use App\Models\Estancia;
use App\Models\NotaCompetenciaTec;
use Illuminate\Support\Facades\DB;

class CalcularNotasCompetenciasTecnicas {
  /**
   * Calcula las notas de los RAs de un alumno basándose en las notas de sus competencias técnicas
   */
  public function calcularNotasTecnicas($alumno_id) {
    $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();
    $notasCompetencias = NotaCompetenciaTec::where('estancia_id', $estancia->id)
      ->with('competenciaTec')
      ->get();

    if ($notasCompetencias->isEmpty()) {
      return [];
    }

    $notasRA = [];

    foreach ($notasCompetencias as $notaCompetencia) {
      // Unir competencia -> RA -> asignatura
      $resultadosAprendizaje = DB::table('competencias_tec_ra as ctr')
        ->join('resultados_aprendizaje as ra', 'ctr.resultado_aprendizaje_id', '=', 'ra.id')
        ->join('asignaturas as a', 'ra.asignatura_id', '=', 'a.id')
        ->where('ctr.competencia_tec_id', $notaCompetencia->competencia_tec_id)
        ->select('ctr.resultado_aprendizaje_id', 'ra.asignatura_id', 'nombre_asignatura')
        ->get();

      foreach ($resultadosAprendizaje as $ra) {
        $raId = $ra->resultado_aprendizaje_id;
        $asignaturaId = $ra->asignatura_id;
        $asignaturaNombre = $ra->nombre_asignatura;

        if (!isset($notasRA[$raId])) {
          $notasRA[$raId] = [
            'suma' => 0,
            'cantidad' => 0,
            'asignatura_id' => $asignaturaId,
            'nombre_asignatura' => $asignaturaNombre,
          ];
        }

        $notasRA[$raId]['suma'] += (float) $notaCompetencia->nota;
        $notasRA[$raId]['cantidad']++;
      }
    }

    // Calcular la media de cada RA
    $mediasRA = [];
    foreach ($notasRA as $raId => $datos) {
      $mediasRA[$raId] = [
        'media_ra' => $datos['suma'] / $datos['cantidad'],
        'asignatura_id' => $datos['asignatura_id'],
        'nombre_asignatura' => $datos['nombre_asignatura'],
      ];
    }

    // Agrupar por asignatura y calcular media de RAs
    $notasPorAsignatura = [];
    foreach ($mediasRA as $raId => $datos) {
      $asignaturaId = $datos['asignatura_id'];

      if (!isset($notasPorAsignatura[$asignaturaId])) {
        $notasPorAsignatura[$asignaturaId] = [
          'suma_medias_ra' => 0,
          'cantidad_ras' => 0,
          'nombre' => $datos['nombre_asignatura'],
        ];
      }

      $notasPorAsignatura[$asignaturaId]['suma_medias_ra'] += $datos['media_ra'];
      $notasPorAsignatura[$asignaturaId]['cantidad_ras']++;
    }

    // Calcular media final por asignatura
    $resultadoFinal = [];
    foreach ($notasPorAsignatura as $asignaturaId => $datos) {
      $mediaAsignatura = $datos['suma_medias_ra'] / $datos['cantidad_ras'];

      $resultadoFinal[] = [
        'asignatura_id' => $asignaturaId,
        'nombre_asignatura' => $datos['nombre'],
        'nota_media' => round($this->convertirAEscala10($mediaAsignatura), 2),
      ];
    }

    return $resultadoFinal;
  }

  private function convertirAEscala10($nota) {
    return round($nota * 2.5, 2);
  }
}
