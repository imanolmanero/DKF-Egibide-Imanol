<?php

namespace App\Http\Controllers;

use App\Models\Alumnos;
use App\Models\Estancia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AlumnosController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Alumnos::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'nombre' => ['required'],
            'apellidos' => ['required'],
            'telefono' => ['required'],
            'ciudad' => ['required'],
            'curso' => ['required'],
            'tutor' => ['required']
        ]);

        // Generar email y contraseña temporal
        $email = strtolower($validated['nombre']) . '.' . strtolower(explode(' ', $validated['apellidos'])[0]) . '@ikasle.egibide.org';
        $password = Hash::make('12345Abcde');

        // USUARIO
        $user = User::create([
            'email' => $email,
            'password' => $password,
            'role' => 'alumno',
        ]);

        // ALUMNO
        $alumno = Alumnos::create([
            'nombre' => $validated['nombre'],
            'apellidos' => $validated['apellidos'],
            'ciudad' => $validated['ciudad'],
            'telefono' => $validated['telefono'],
            'user_id' => $user->id,
        ]);

        // ESTANCIA
        Estancia::create([
            'puesto' => 'Sin asignar',
            'fecha_inicio' => now()->toDateString(),
            'horas_totales' => 0,
            'alumno_id' => $alumno->id,
            'tutor_id' => $validated['tutor'],
            'curso_id' => $validated['curso'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alumno agregado correctamente'
        ], 201);
    }

    public function inicio(Request $request) {
        $user = $request->user();

        $alumno = $user->alumno;

        if (!$alumno) {
            return response()->json([
                'message' => 'El usuario no tiene alumno asociado.'
            ], 404);
        }

        $hoy = now();

        $estanciaActual = $alumno->estancias()
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        if (!$estanciaActual) {
            $estanciaActual = $alumno->estancias()
                ->orderBy('fecha_inicio', 'desc')
                ->first();
        }

        if (!$estanciaActual) {
            return response()->json([
                'alumno' => [
                    'nombre' => $alumno->nombre,
                    'apellidos' => $alumno->apellidos,
                ],
                'estancia' => null,
                'message' => 'El alumno no tiene estancias asignadas todavía.'
            ]);
        }

        $estanciaActual->load([
            'empresa:id,nombre',
            'tutor:id,nombre,apellidos,telefono',
            'instructor:id,nombre,apellidos,telefono,empresa_id',
            'horariosDia.horariosTramo',
        ]);

        return response()->json([
            'alumno' => [
                'nombre' => $alumno->nombre,
                'apellidos' => $alumno->apellidos,
            ],
            'estancia' => [
                'fecha_inicio' => optional($estanciaActual->fecha_inicio)->toDateString(),
                'fecha_fin' => optional($estanciaActual->fecha_fin)->toDateString(),
                'puesto' => $estanciaActual->puesto,
                'empresa' => $estanciaActual->empresa ? [
                    'nombre' => $estanciaActual->empresa->nombre,
                ] : null,
                'tutor' => $estanciaActual->tutor ? [
                    'nombre' => $estanciaActual->tutor->nombre,
                    'apellidos' => $estanciaActual->tutor->apellidos,
                    'telefono' => $estanciaActual->tutor->telefono,
                ] : null,
                'instructor' => $estanciaActual->instructor ? [
                    'nombre' => $estanciaActual->instructor->nombre,
                    'apellidos' => $estanciaActual->instructor->apellidos,
                    'telefono' => $estanciaActual->instructor->telefono,
                ] : null,
                'horario' => $estanciaActual->horariosDia->map(function ($dia) {
                    return [
                        'dia_semana' => $dia->dia_semana,
                        'tramos' => $dia->horariosTramo->map(function ($t) {
                            return [
                                'hora_inicio' => $t->hora_inicio,
                                'hora_fin' => $t->hora_fin,
                            ];
                        })->values()
                    ];
                })->values(),
            ],
        ]);
    }

    public function me() {
        $userId = auth()->id();

        $row = Alumnos::join('users', 'alumnos.user_id', '=', 'users.id')
            ->select(
                'alumnos.nombre',
                'alumnos.apellidos',
                'alumnos.telefono',
                'alumnos.ciudad',

                'users.email',
            )
            ->where('alumnos.user_id', $userId)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Alumno no encontrado'], 404);
        }

        return response()->json($row);
    }

    public function getAsignaturasAlumno($alumno_id) {
        $estancia = Estancia::where('alumno_id', $alumno_id)
            ->with('curso.ciclo.asignaturas')
            ->firstOrFail();

        $asignaturas = $estancia->curso->ciclo->asignaturas;

        return response()->json($asignaturas, 200);
    }

    public function entregas($alumnoId) {
        $entregas = DB::table('entregas')
            ->join('cuadernos_practicas', 'entregas.cuaderno_practicas_id', '=', 'cuadernos_practicas.id')
            ->join('estancias', 'cuadernos_practicas.estancia_id', '=', 'estancias.id')
            ->where('estancias.alumno_id', $alumnoId)
            ->select('entregas.id', 'entregas.archivo', 'entregas.fecha')
            ->orderBy('entregas.fecha', 'desc')
            ->get();

        return response()->json($entregas);
    }
}
