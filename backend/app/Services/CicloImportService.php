<?php

namespace App\Services;

use App\Models\Ciclos;
use App\Models\Curso;
use Illuminate\Http\UploadedFile;

class CicloImportService
{
    /**
     * Genera una plantilla CSV de ejemplo para importar ciclos
     *
     * @return string
     */
    public function generarPlantillaCSV(): string
    {
        $headers = ['numero', 'nombre', 'descripcion'];
        
        $csv = implode(',', $headers) . "\n";
        $csv .= "1,DAW,Desarrollo de Aplicaciones Web\n";
        $csv .= "2,ASIX,Administración de Sistemas Informáticos en Red\n";
        
        return $csv;
    }

    /**
     * Importa ciclos desde un archivo CSV
     *
     * @param int $cicloId ID del ciclo a importar
     * @param UploadedFile $file Archivo CSV
     * @return array
     * @throws \Exception
     */
    public function importarDesdeCSV(int $cicloId, UploadedFile $file): array
    {
        $ciclo = Ciclos::findOrFail($cicloId);
        
        $contenido = $file->get();
        $lineas = explode("\n", $contenido);
        
        $insertados = 0;
        $omitidos = 0;
        
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            
            if (empty($linea)) {
                continue;
            }
            
            $datos = str_getcsv($linea);
            
            if (count($datos) < 2) {
                $omitidos++;
                continue;
            }
            
            try {
                Curso::firstOrCreate(
                    [
                        'ciclo_id' => $ciclo->id,
                        'numero' => (int) $datos[0],
                    ],
                    [
                        'nombre' => $datos[1] ?? null,
                        'descripcion' => $datos[2] ?? null,
                    ]
                );
                
                $insertados++;
            } catch (\Exception $e) {
                $omitidos++;
            }
        }
        
        return [
            'insertados' => $insertados,
            'omitidos' => $omitidos,
        ];
    }
}
