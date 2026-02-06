<?php

namespace App\Http\Controllers;

use App\Models\Ciclos;
use App\Models\Curso;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

class CiclosController extends Controller {
    protected $importService;


    
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Ciclos::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'nombre' => ['required'],
            'familia_profesional_id' => ['required']
        ]);

        Ciclos::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ciclo agregado'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ciclos $ciclo) {
        $ciclo->load('familiaProfesional');

        return response()->json([
            'id' => $ciclo->id,
            'nombre' => $ciclo->nombre,
            'familia_profesional_id' => $ciclo->familia_profesional_id,
            'familia_profesional' => $ciclo->familiaProfesional ? $ciclo->familiaProfesional->nombre : null,
        ]);
    }

    public function getCursosByCiclos($ciclo_id) {
        $ciclo = Ciclos::find($ciclo_id);

        if (!$ciclo) {
            return response()->json(['message' => 'Ciclo no encontrado'], 404);
        }

        $cursos = $ciclo->cursos;

        return response()->json($cursos, 200);
    }

    public function importarCSV(Request $request) {
        $validator = Validator::make($request->all(), [
            'ciclo_id' => 'required',
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cicloId = $request->input('ciclo_id');
            $file = $request->file('csv_file');

            $resultado = $this->importService->importarDesdeCSV($cicloId, $file);

            return response()->json([
                'success' => true,
                'message' => 'Importación completada exitosamente',
                'datos' => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error en la importación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar plantilla CSV de ejemplo
     *
     * @return \Illuminate\Http\Response
     */
    public function descargarPlantillaCSV() {
        $csvContent = $this->importService->generarPlantillaCSV();

        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="plantilla_ciclo.csv"');
    }
}
