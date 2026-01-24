<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Exception;

class CicloImportService {
  /**
   * Importa datos del ciclo desde un archivo CSV
   *
   * @param int $cicloId
   * @param UploadedFile $file
   * @return array
   * @throws Exception
   */
  public function importarDesdeCSV(int $cicloId, UploadedFile $file): array {
    // Verificar que el ciclo existe
    $ciclo = DB::table('ciclos')->where('id', $cicloId)->first();
    if (!$ciclo) {
      throw new Exception("El ciclo con ID {$cicloId} no existe");
    }

    // Leer y procesar el archivo
    $csvData = $this->leerCSV($file);

    // Procesar datos dentro de una transacción
    DB::beginTransaction();

    try {
      $resultado = $this->procesarDatos($csvData, $cicloId);
      DB::commit();

      return $resultado;
    } catch (Exception $e) {
      DB::rollBack();
      throw $e;
    }
  }

  /**
   * Lee y parsea el archivo CSV
   *
   * @param UploadedFile $file
   * @return array
   */
  private function leerCSV(UploadedFile $file): array {
    // Detectar encoding y convertir a UTF-8 si es necesario
    $content = file_get_contents($file->getRealPath());
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

    if ($encoding !== 'UTF-8') {
      $content = mb_convert_encoding($content, 'UTF-8', $encoding);
      file_put_contents($file->getRealPath(), $content);
    }

    // Leer CSV
    $csvData = array_map('str_getcsv', file($file->getRealPath()));

    // Remover header
    array_shift($csvData);

    return $csvData;
  }

  /**
   * Procesa los datos del CSV
   *
   * @param array $data
   * @param int $cicloId
   * @return array
   */
  private function procesarDatos(array $data, int $cicloId): array {
    $stats = [
      'asignaturas_creadas' => 0,
      'competencias_creadas' => 0,
      'resultados_creados' => 0,
      'relaciones_creadas' => 0,
      'filas_procesadas' => 0,
      'errores' => []
    ];

    // Cache para evitar consultas repetidas
    $asignaturasCache = [];
    $competenciasCache = [];

    foreach ($data as $index => $row) {
      $lineNumber = $index + 2; // +2 porque índice empieza en 0 y header

      // Validar que la fila tenga las 5 columnas esperadas
      if (count($row) < 5) {
        if (!empty(array_filter($row))) { // Solo reportar si no es línea vacía
          $stats['errores'][] = "Línea {$lineNumber}: Fila incompleta (se esperan 5 columnas)";
        }
        continue;
      }

      try {
        $codigoAsignatura = trim($row[0]);
        $nombreAsignatura = trim($row[1]);
        $codigoCompetencia = trim($row[2]);
        $descripcionCompetencia = trim($row[3]);
        $descripcionResultado = trim($row[4]);

        // Validar campos obligatorios
        if (
          empty($codigoAsignatura) || empty($codigoCompetencia) ||
          empty($descripcionCompetencia) || empty($descripcionResultado)
        ) {
          $stats['errores'][] = "Línea {$lineNumber}: Campos obligatorios vacíos";
          continue;
        }

        // 1. Procesar ASIGNATURA
        $asignaturaId = $this->procesarAsignatura(
          $codigoAsignatura,
          $nombreAsignatura,
          $cicloId,
          $asignaturasCache,
          $stats
        );

        // 2. Procesar COMPETENCIA TÉCNICA
        $competenciaId = $this->procesarCompetencia(
          $codigoCompetencia,
          $descripcionCompetencia,
          $cicloId,
          $competenciasCache,
          $stats
        );

        // 3. Procesar RESULTADO DE APRENDIZAJE
        $resultadoId = $this->procesarResultadoAprendizaje(
          $descripcionResultado,
          $asignaturaId,
          $stats
        );

        // 4. Crear RELACIÓN
        $this->crearRelacion($competenciaId, $resultadoId, $stats);

        $stats['filas_procesadas']++;
      } catch (Exception $e) {
        $stats['errores'][] = "Línea {$lineNumber}: {$e->getMessage()}";
      }
    }

    return $stats;
  }

  /**
   * Procesa una asignatura (busca o crea)
   */
  private function procesarAsignatura(
    string $codigo,
    string $nombre,
    int $cicloId,
    array &$cache,
    array &$stats
  ): int {
    if (!isset($cache[$codigo])) {
      $asignatura = DB::table('asignaturas')
        ->where('codigo_asignatura', $codigo)
        ->where('ciclo_id', $cicloId)
        ->first();

      if (!$asignatura) {
        $asignaturaId = DB::table('asignaturas')->insertGetId([
          'codigo_asignatura' => $codigo,
          'nombre_asignatura' => $nombre,
          'ciclo_id' => $cicloId,
          'created_at' => now(),
          'updated_at' => now()
        ]);
        $stats['asignaturas_creadas']++;
      } else {
        $asignaturaId = $asignatura->id;
      }

      $cache[$codigo] = $asignaturaId;
    }

    return $cache[$codigo];
  }

  /**
   * Procesa una competencia técnica (busca o crea por código)
   */
  private function procesarCompetencia(
    string $codigo,
    string $descripcion,
    int $cicloId,
    array &$cache,
    array &$stats
  ): int {
    if (!isset($cache[$codigo])) {
      // Buscar por descripción la primera vez que vemos este código
      $competencia = DB::table('competencias_tec')
        ->where('descripcion', $descripcion)
        ->where('ciclo_id', $cicloId)
        ->first();

      if (!$competencia) {
        $competenciaId = DB::table('competencias_tec')->insertGetId([
          'descripcion' => $descripcion,
          'ciclo_id' => $cicloId,
          'created_at' => now(),
          'updated_at' => now()
        ]);
        $stats['competencias_creadas']++;
      } else {
        $competenciaId = $competencia->id;
      }

      // Guardar en cache por CÓDIGO (esto previene duplicados por diferencias en texto)
      $cache[$codigo] = $competenciaId;
    }

    return $cache[$codigo];
  }

  /**
   * Procesa un resultado de aprendizaje (busca o crea)
   */
  private function procesarResultadoAprendizaje(
    string $descripcion,
    int $asignaturaId,
    array &$stats
  ): int {
    $resultado = DB::table('resultados_aprendizaje')
      ->where('descripcion', $descripcion)
      ->where('asignatura_id', $asignaturaId)
      ->first();

    if (!$resultado) {
      $resultadoId = DB::table('resultados_aprendizaje')->insertGetId([
        'descripcion' => $descripcion,
        'asignatura_id' => $asignaturaId,
        'created_at' => now(),
        'updated_at' => now()
      ]);
      $stats['resultados_creados']++;
    } else {
      $resultadoId = $resultado->id;
    }

    return $resultadoId;
  }

  /**
   * Crea la relación entre competencia y resultado de aprendizaje
   */
  private function crearRelacion(int $competenciaId, int $resultadoId, array &$stats): void {
    $relacionExiste = DB::table('competencias_tec_ra')
      ->where('competencia_tec_id', $competenciaId)
      ->where('resultado_aprendizaje_id', $resultadoId)
      ->exists();

    if (!$relacionExiste) {
      DB::table('competencias_tec_ra')->insert([
        'competencia_tec_id' => $competenciaId,
        'resultado_aprendizaje_id' => $resultadoId,
        'created_at' => now(),
        'updated_at' => now()
      ]);
      $stats['relaciones_creadas']++;
    }
  }

  /**
   * Genera una plantilla CSV de ejemplo
   *
   * @return string
   */
  public function generarPlantillaCSV(): string {
    $lineas = [
      'codigo_asignatura,nombre_asignatura,codigo_competencia,descripcion_competencia,descripcion_resultado',
      'DWEC,Desarrollo web cliente,COMP2,Desarrolla el frontend de una aplicación web utilizando lenguajes y tecnologías web adecuadas,Selecciona las arquitecturas y tecnologías de programación sobre clientes web',
      'DWEC,Desarrollo web cliente,COMP2,Desarrolla el frontend de una aplicación web utilizando lenguajes y tecnologías web adecuadas,Escribe sentencias simples aplicando la sintaxis del lenguaje',
      'DIW,Diseño de interfaces web,COMP2,Desarrolla el frontend de una aplicación web utilizando lenguajes y tecnologías web adecuadas,Planifica la creación de una interfaz web valorando diseño',
      'DWES,Desarrollo web servidor,COMP3,Desarrolla la lógica de negocio de una aplicación web utilizando lenguajes de programación,Selecciona las arquitecturas y tecnologías web en entorno servidor'
    ];

    return implode("\n", $lineas);
  }
}
