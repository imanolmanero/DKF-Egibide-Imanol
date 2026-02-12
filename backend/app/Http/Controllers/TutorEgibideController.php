<?php

namespace App\Http\Controllers;

use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\TutorEgibide;
use App\Models\Estancia;
use App\Models\Empresas;
use App\Models\HorarioDia;
use App\Models\HorarioTramo;
use App\Models\TutorEmpresa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;


class TutorEgibideController extends Controller
{

    public function getAlumnosByCurrentTutor(Request $request)
    {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $alumnos = $tutor->alumnos()->with('estancias.empresa')->get();

        return response()->json($alumnos);
    }

    public function conseguirEmpresasporTutor(Request $request)
    {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $empresas = Empresas::whereHas('estancias.alumno', function ($query) use ($tutor) {
            $query->where('tutor_id', $tutor->id);
        })->get();

        return response()->json($empresas);
    }


    public function getDetalleEmpresa(Request $request, $empresaId)
    {
        $empresa = Empresas::with('instructores')->findOrFail($empresaId);
        return response()->json($empresa);
    }


    public function getTutoresByCiclo($ciclo_id)
    {
        $ciclo = Ciclos::find($ciclo_id);
        if (!$ciclo)
            return response()->json([], 404);

        $tutores = $ciclo->tutores;

        return response()->json($tutores, 200);
    }

    public function inicioTutor(Request $request)
    {
        $user = $request->user();

        $tutor = $user->tutorEgibide;

        if (!$tutor) {
            return response()->json([
                'message' => 'El usuario no tiene tutor egibide asociado.',
                'user' => $user
            ], 404);
        }

        $email = $user->email;
        $tutor['email'] = $email;
        $hoy = now();

        $alumnosAsignados = $tutor->alumnos()->count();
        $alumnosConEstancia = $tutor->alumnos()
            ->whereHas('estancias', function ($q) use ($hoy) {
                $q->whereDate('fecha_inicio', '<=', $hoy)
                    ->where(function ($q2) use ($hoy) {
                        $q2->whereNull('fecha_fin')
                            ->orWhereDate('fecha_fin', '>=', $hoy);
                    })
                    ->whereNotNull('empresa_id');
            })
            ->count();


        return response()->json([
            'tutor' => $tutor,
            'counts' => [
                'alumnos_asignados' => $alumnosAsignados,
                'empresas_asignadas' => $alumnosConEstancia,
            ],
        ]);
    }

    public function asignarAlumno(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'tutor_id' => 'required|exists:users,id', // suponiendo que tutores son usuarios
        ]);

        $alumno = Alumnos::find($request->alumno_id);
        $tutorId = $request->tutor_id;

        try {
            // Asignación simple, suponiendo campo tutor_id en tabla alumnos
            $alumno->tutor_id = $tutorId;
            $alumno->save();

            return response()->json([
                'success' => true,
                'message' => 'Alumno asignado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar alumno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
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
    public function horasperiodo(Request $request)
    {
        $validated = $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'horario' => 'required|array',
            'horario.*.dia' => 'required|string',
            'horario.*.franjas' => 'required|array',
            'horario.*.franjas.*.hora_inicial' => 'required|integer|min:0|max:23',
            'horario.*.franjas.*.hora_final' => 'required|integer|min:0|max:23',
        ]);

        try {

            // 1️⃣ Crear o actualizar estancia
            $estancia = Estancia::updateOrCreate(
                ['alumno_id' => $validated['alumno_id']],
                [
                    'fecha_inicio' => $validated['fecha_inicio'],
                    'fecha_fin' => $validated['fecha_fin'] ?? null,
                ]
            );

            // 2️⃣ Borrar horario anterior si existe
            $estancia->horariosDia()->each(function ($dia) {
                $dia->horariosTramo()->delete();
                $dia->delete();
            });

            $horasTotales = 0;

            // 3️⃣ Crear nuevos horarios
            foreach ($validated['horario'] as $diaData) {

                $horarioDia = HorarioDia::create([
                    'dia_semana' => strtolower($diaData['dia']),
                    'estancia_id' => $estancia->id,
                ]);

                foreach ($diaData['franjas'] as $franja) {

                    if ($franja['hora_final'] <= $franja['hora_inicial']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Hora fin debe ser mayor que hora inicio en {$diaData['dia']}"
                        ], 422);
                    }

                    // Convertimos a formato HH:00:00
                    $horaInicio = str_pad($franja['hora_inicial'], 2, '0', STR_PAD_LEFT) . ':00:00';
                    $horaFin = str_pad($franja['hora_final'], 2, '0', STR_PAD_LEFT) . ':00:00';

                    HorarioTramo::create([
                        'horario_dia_id' => $horarioDia->id,
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin,
                    ]);

                    $horasTotales += $franja['hora_final'] - $franja['hora_inicial'];
                }
            }

            // 4️⃣ Actualizar horas totales
            $estancia->update([
                'horas_totales' => $horasTotales
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Horario guardado correctamente',
                'estancia' => $estancia->load('horariosDia.horariosTramo')
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getHorarioAlumno($alumnoId)
    {
        $estancia = Estancia::with('horariosDia.horariosTramo')->where('alumno_id', $alumnoId)->first();

        if (!$estancia) {
            return response()->json(null, 200);
        }

        $horario = [];

        foreach ($estancia->horariosDia as $dia) {
            $franjas = $dia->horariosTramo->map(function ($tramo) {
                return [
                    'hora_inicial' => (int)explode(':', $tramo->hora_inicio)[0],
                    'hora_final' => (int)explode(':', $tramo->hora_fin)[0],
                ];
            })->toArray();

            $horario[] = [
                'dia' => ucfirst($dia->dia_semana),
                'franjas' => $franjas,
            ];
        }

        return response()->json([
            'fecha_inicio' => $estancia->fecha_inicio,
            'fecha_fin' => $estancia->fecha_fin,
            'horario' => $horario
        ]);
    }


    public function getMisCursosConAlumnosSinTutor(Request $req, $tutorId)
    {
        $tutor = TutorEgibide::find($tutorId);

        // Obtenemos los cursos del tutor con su ciclo y alumnos sin tutor asignado
        $cursos = $tutor->ciclos()
            ->with([
                'alumnos' => function ($query) {
                    $query->whereNull('tutor_id'); // solo alumnos sin tutor
                }
            ])
            ->get();

        return response()->json($cursos);
    }

    /**
     * NUEVO: Obtener instructores disponibles para asignar a un alumno
     * Retorna los instructores de la empresa donde está asignado el alumno
     */
    public function getInstructoresParaAlumno(Request $request, $alumnoId)
    {
        Log::info('Obteniendo instructores para alumno', ['alumno_id' => $alumnoId]);

        try {
            $alumno = Alumnos::with('estancias.empresa')->findOrFail($alumnoId);

            // Obtener la estancia actual del alumno
            $estancia = $alumno->estancias()->latest()->first();

            if (!$estancia || !$estancia->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El alumno no tiene una empresa asignada. Asigna primero una empresa.',
                    'instructores' => []
                ], 200);
            }

            // Obtener todos los instructores de la empresa
            $instructores = TutorEmpresa::where('empresa_id', $estancia->empresa_id)
                ->with('empresa')
                ->get();

            Log::info('Instructores encontrados', [
                'empresa_id' => $estancia->empresa_id,
                'count' => $instructores->count()
            ]);

            return response()->json([
                'success' => true,
                'empresa' => $estancia->empresa,
                'instructores' => $instructores,
                'instructor_actual_id' => $estancia->instructor_id
            ], 200);

        } catch (Exception $e) {
            Log::error('Error al obtener instructores para alumno', [
                'alumno_id' => $alumnoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener instructores: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * NUEVO: Asignar un instructor a un alumno (actualiza la estancia)
     */
    public function asignarInstructorAlumno(Request $request)
    {
        Log::info('Asignando instructor a alumno', ['request_data' => $request->all()]);

        try {
            $validated = $request->validate([
                'alumno_id' => 'required|exists:alumnos,id',
                'instructor_id' => 'required|exists:instructores,id',
            ]);

            Log::info('Validación exitosa', ['validated' => $validated]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($validated) {
                // Obtener el alumno
                $alumno = Alumnos::findOrFail($validated['alumno_id']);

                // Obtener la estancia actual del alumno
                $estancia = $alumno->estancias()->latest()->first();

                if (!$estancia) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El alumno no tiene una estancia registrada'
                    ], 404);
                }

                // Verificar que el instructor pertenece a la misma empresa
                $instructor = TutorEmpresa::findOrFail($validated['instructor_id']);

                if ($estancia->empresa_id && $instructor->empresa_id !== $estancia->empresa_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El instructor no pertenece a la empresa asignada al alumno'
                    ], 400);
                }

                // Actualizar el instructor en la estancia
                $estancia->instructor_id = $validated['instructor_id'];
                $estancia->save();

                Log::info('Instructor asignado exitosamente', [
                    'estancia_id' => $estancia->id,
                    'instructor_id' => $validated['instructor_id']
                ]);

                // Cargar la relación del instructor para retornar
                $estancia->load('instructor');

                return response()->json([
                    'success' => true,
                    'message' => 'Instructor asignado correctamente al alumno',
                    'estancia' => $estancia,
                    'instructor' => $instructor
                ], 200);
            });

        } catch (Exception $e) {
            Log::error('Error al asignar instructor', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el instructor: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}