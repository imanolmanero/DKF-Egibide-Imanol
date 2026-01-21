<?php

namespace App\Http\Controllers;

use App\Models\Alumnos;
use App\Models\Asignatura;
use App\Models\Estancia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'ciudad' => ['required']
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

        return response()->json([
            'success' => true,
            'message' => 'Alumno agregado correctamente'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Alumnos $alumnos) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alumnos $alumnos) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Alumnos $alumnos) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alumnos $alumnos) {
        //
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

    public function notaCuadernoLogeado() {
        $user = auth()->user();
        if (!$user) return response()->json(['message' => 'No autenticado'], 401);

        $alumno = $user->alumno;
        if (!$alumno) {
            return response()->json(['nota' => null, 'message' => 'Sin alumno asociado'], 200);
        }

        $estancia = $alumno->estancias()->orderByDesc('fecha_fin')->first();
        if (!$estancia) {
            return response()->json(['nota' => null, 'message' => 'No hay estancia para este alumno todavía.'], 200);
        }

        if ($estancia->fecha_fin && $estancia->fecha_fin > now()->toDateString()) {
            return response()->json(['nota' => null, 'message' => 'La estancia aún no ha finalizado.'], 200);
        }

        $cuaderno = $estancia->cuadernoPracticas;
        if (!$cuaderno) {
            return response()->json(['nota' => null, 'message' => 'No hay cuaderno asociado.'], 200);
        }
        $nota = $cuaderno->nota?->nota;

        return response()->json([
            'nota' => $nota !== null ? (float) $nota : null,
            'message' => $nota === null ? 'La estancia ha finalizado, pero aún no hay nota del cuaderno.' : null
        ], 200);
    }

    public function getAsignaturasAlumno($alumno_id) {
        $estancia = Estancia::where('alumno_id', $alumno_id)
            ->with('curso.ciclo.asignaturas')
            ->firstOrFail();

        $asignaturas = $estancia->curso->ciclo->asignaturas;

        return response()->json($asignaturas, 200);
    }
}
