<?php

namespace App\Http\Controllers;

use App\Models\Estancia;
use App\Models\TutorEmpresa;
use Illuminate\Http\Request;

class TutorEmpresaController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    public function getAlumnosByCurrentInstructor(Request $request) {
        $userId = $request->user()->id;

        $instructor = TutorEmpresa::where('user_id', $userId)->firstOrFail();

        $alumnos = $instructor->alumnosConEstancia()->get();

        return response()->json($alumnos);
    }

    public function inicioInstructor(Request $request)
{
    $user = $request->user();

    $instructor = $user->instructor;

    if (!$instructor) {
        return response()->json([
            'message' => 'El usuario no tiene instructor (tutor de empresa) asociado.',
            'user' => $user
        ], 404);
    }

    $email = $user->email;
    $hoy = now();

    $alumnosAsignados = $instructor->estancias()
        ->whereDate('fecha_inicio', '<=', $hoy)
        ->where(function ($q) use ($hoy) {
            $q->whereNull('fecha_fin')
              ->orWhereDate('fecha_fin', '>=', $hoy);
        })
        ->whereNotNull('alumno_id')
        ->distinct()
        ->count('alumno_id');

    return response()->json([
        'instructor' => [
            'nombre'    => $instructor->nombre,
            'apellidos' => $instructor->apellidos,
            'telefono'  => $instructor->telefono,
            'ciudad'    => $instructor->ciudad ?? null,
            'email'     => $email,
        ],
        'counts' => [
            'alumnos_asignados' => $alumnosAsignados,
        ],
    ]);
}

    /**
     * Display the specified resource.
     */
    public function show(TutorEmpresa $tutorEmpresa) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TutorEmpresa $tutorEmpresa) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TutorEmpresa $tutorEmpresa) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TutorEmpresa $tutorEmpresa) {
        //
    }

    public function me(Request $request) {
        $user = $request->user();

        $tutor = TutorEmpresa::where('user_id', $user->id)->first();

        return response()->json([
            'id' => $tutor->id,
            'nombre' => $tutor->nombre,
            'apellidos' => $tutor->apellidos,
            'email' => $user->email,
            'tipo' => $user->role,
        ]);
    }
}
