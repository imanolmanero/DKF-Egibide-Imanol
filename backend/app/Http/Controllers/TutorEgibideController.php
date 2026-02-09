<?php

namespace App\Http\Controllers;

use App\Models\Ciclos;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\TutorEgibide;
use App\Models\Estancia;
use App\Models\Empresas;


class TutorEgibideController extends Controller {

    public function getAlumnosByCurrentTutor(Request $request) {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $alumnos = $tutor->alumnosConEstancia()->get();

        return response()->json($alumnos);
    }

    public function conseguirEmpresasporTutor(Request $request) {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        // Obtener empresas únicas a través de las estancias
        $empresas = Empresas::whereHas('estancias', function ($query) use ($tutor) {
            $query->where('tutor_id', $tutor->id);
        })->get();

        return response()->json($empresas);
    }

    public function getDetalleEmpresa(Request $request, $empresaId) {
        $userId = $request->user()->id;
        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        // Obtener empresa con instructor
        $empresa = Empresas::with(['instructores' => function ($query) use ($tutor) {
            $query->whereHas('estancias', function ($q) use ($tutor) {
                $q->where('tutor_id', $tutor->id);
            });
        }])
            ->where('id', $empresaId)
            ->whereHas('estancias', function ($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id);
            })
            ->firstOrFail();

        return response()->json($empresa);
    }

    public function getTutoresByCiclo($ciclo_id) {
        $ciclo = Ciclos::find($ciclo_id);
        if (!$ciclo) return response()->json([], 404);

        $familia = $ciclo->familiaProfesional;
        $tutores = $familia?->tutores ?? collect();

        return response()->json($tutores, 200);
    }

    public function inicioTutor(Request $request) {
        $user = $request->user();

        $tutor = $user->tutorEgibide;

        if (!$tutor) {
            return response()->json([
                'message' => 'El usuario no tiene tutor egibide asociado.'
            ], 404);
        }

        $email = $user->email;
        $hoy = now();

        $alumnosAsignados = $tutor->estancias()
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->whereNotNull('alumno_id')
            ->distinct()
            ->count('alumno_id');

        $empresasAsignadas = $tutor->estancias()
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->whereNotNull('empresa_id')
            ->distinct()
            ->count('empresa_id');

        return response()->json([
            'tutor' => [
                'nombre'    => $tutor->nombre,
                'apellidos' => $tutor->apellidos,
                'telefono'  => $tutor->telefono,
                'ciudad'    => $tutor->ciudad,
                'email'     => $email,
            ],
            'counts' => [
                'alumnos_asignados'  => $alumnosAsignados,
                'empresas_asignadas' => $empresasAsignadas,
            ],
        ]);
    }


    public function me(Request $request) {
        $user = $request->user();

        $tutor = TutorEgibide::where('user_id', $user->id)->first();

        return response()->json([
            'id' => $tutor->id,
            'nombre' => $tutor->nombre,
            'apellidos' => $tutor->apellidos,
            'email' => $user->email,
            'tipo' => $user->role,
        ]);
    }

    /**
     * Guardar o actualizar horario y calendario de una estancia.
     */
    public function horasperiodo(Request $request) {
        // Validar los datos
        $validated = $request->validate([
            'alumno_id'     => 'required|exists:alumnos,id',
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'nullable|date|after_or_equal:fecha_inicio',
            'horas_totales' => 'required|integer|min:1',
        ]);

        try {
            // Obtener tutor logueado
            $user = $request->user();
            $tutor = TutorEgibide::where('user_id', $user->id)->firstOrFail();

            // Crear o actualizar estancia por alumno_id
            $estancia = Estancia::updateOrCreate(
                ['alumno_id' => $validated['alumno_id']], // Condición para actualizar
                [
                    'fecha_inicio'  => $validated['fecha_inicio'],
                    'fecha_fin'     => $validated['fecha_fin'] ?? null,
                    'horas_totales' => $validated['horas_totales'],
                    'tutor_id'      => $tutor->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Horario y calendario guardados correctamente',
                'estancia' => $estancia,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la estancia: ' . $e->getMessage(),
            ], 500);
        }
    }
}
