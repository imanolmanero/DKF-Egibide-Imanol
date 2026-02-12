<?php

namespace App\Http\Controllers;

use App\Models\CompetenciaTec;
use App\Models\CompetenciaTransversal;
use App\Models\Estancia;
use App\Models\NotaCompetenciaTec;
use App\Models\NotaCompetenciaTransversal;
use Illuminate\Http\Request;

class CompetenciasController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $competenciasTec = CompetenciaTec::all()->map(function ($c) {
            return [
                'id' => $c->id,
                'descripcion' => $c->descripcion,
                'tipo' => 'TECNICA',
            ];
        });

        $competenciasTrans = CompetenciaTransversal::all()->map(function ($c) {
            return [
                'id' => $c->id,
                'descripcion' => $c->descripcion,
                'tipo' => 'TRANSVERSAL',
            ];
        });

        return response()->json(
            $competenciasTec
                ->merge($competenciasTrans)
                ->values()
        );
    }

    public function getCompetenciasTecnicasByAlumno($alumno_id)
    {
        $estancia = Estancia::with('alumno.ciclo')->where('alumno_id', $alumno_id)->firstOrFail();

        $alumno = $estancia->alumno;
        if (!$alumno || !$alumno->ciclo) {
            return response()->json([]);
        }

        $cicloId = $alumno->ciclo->id;

        $competenciasTec = CompetenciaTec::where('ciclo_id', $cicloId)->get();

        return response()->json($competenciasTec);
    }



   public function getCompetenciasTecnicasAsignadasByAlumno($alumno_id)
    {
        $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();

        $competenciasTecAsignadas = NotaCompetenciaTec::where('estancia_id', $estancia->id)
            ->with('competenciaTec')
            ->get();

        $resultado = $competenciasTecAsignadas->map(function ($nota) {
            return [
                'nota' => $nota->nota,
                'competencia_tec_id' => $nota->competencia_tec_id,
                'descripcion' => $nota->competenciaTec->descripcion ?? null,
            ];
        });

        return response()->json($resultado);
    }


    public function getCompetenciasTransversalesByAlumno($alumno_id)
    {
        $estancia = Estancia::with('alumno.ciclo')->where('alumno_id', $alumno_id)->firstOrFail();

        $alumno = $estancia->alumno;
        if (!$alumno || !$alumno->ciclo) {
            return response()->json([]);
        }

        $familiaId = $alumno->ciclo->familia_profesional_id;

        $competenciasTrans = CompetenciaTransversal::where('familia_profesional_id', $familiaId)->get();

        return response()->json($competenciasTrans);
    }


    public function getCalificacionesCompetenciasTecnicas($alumno_id) {
        $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();

        $calificacionesTec = NotaCompetenciaTec::where('estancia_id', $estancia->id)->get();

        return response()->json($calificacionesTec);
    }

    public function getCalificacionesCompetenciasTransversales($alumno_id) {
        $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();

        $calificacionesTrans = NotaCompetenciaTransversal::where('estancia_id', $estancia->id)->get();

        return response()->json($calificacionesTrans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeTecnica(Request $request) {
        $validated = $request->validate([
            'ciclo_id' => ['required'],
            'descripcion' => ['required']
        ]);

        CompetenciaTec::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Competencia técnica agregada correctamente'
        ], 201);
    }

    public function storeTransversal(Request $request) {
        $validated = $request->validate([
            'familia_profesional_id' => ['required'],
            'descripcion' => ['required']
        ]);

        CompetenciaTransversal::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Competencia transversal agregada correctamente'
        ], 201);
    }

    public function storeCompetenciasTecnicasAsignadas(Request $request) {
        $validated = $request->validate([
            'alumno_id' => ['required', 'integer'],
            'competencias' => ['required', 'array'],
            'competencias.*' => ['integer']
        ]);

        $alumno_id = $validated['alumno_id'];

        $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();

        foreach ($validated['competencias'] as $compenciaId) {
            NotaCompetenciaTec::updateOrcreate([
                'estancia_id' => $estancia->id,
                'competencia_tec_id' => $compenciaId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Competencias técnicas asignadas correctamente'
        ], 201);
    }

    public function storeCompetenciasTecnicasCalificadas(Request $request) {
        $validated = $request->validate([
            'alumno_id' => ['required', 'integer'],
            'competencias' => ['required', 'array'],
            'competencias.*.competencia_id' => ['required', 'integer'],
            'competencias.*.calificacion' => ['required', 'integer', 'between:1,4'],
        ]);

        $alumno_id = $validated['alumno_id'];

        $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();

        foreach ($validated['competencias'] as $competencia) {

            NotaCompetenciaTec::updateOrCreate(
                [
                    'estancia_id' => $estancia->id,
                    'competencia_tec_id' => $competencia['competencia_id'],
                ],
                [
                    'nota' => $competencia['calificacion']
                ]
            );
        }


        return response()->json([
            'success' => true,
            'message' => 'Competencias técnicas calificadas correctamente'
        ], 201);
    }

    public function storeCompetenciasTransversalesCalificadas(Request $request) {
        $validated = $request->validate([
            'alumno_id' => ['required', 'integer'],
            'competencias' => ['required', 'array'],
            'competencias.*.competencia_id' => ['required', 'integer'],
            'competencias.*.calificacion' => ['required', 'integer', 'between:1,4'],
        ]);

        $alumno_id = $validated['alumno_id'];

        $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();

        foreach ($validated['competencias'] as $competencia) {

            NotaCompetenciaTransversal::updateOrCreate(
                [
                    'estancia_id' => $estancia->id,
                    'competencia_trans_id' => $competencia['competencia_id'],
                ],
                [
                    'nota' => $competencia['calificacion']
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Competencias transversales calificadas correctamente'
        ], 201);
    }
}
