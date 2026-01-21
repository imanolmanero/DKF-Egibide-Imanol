<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\TutorEgibide;
use App\Models\Estancia;


class TutorEgibideController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    public function getAlumnosByCurrentTutor(Request $request) {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $alumnos = $tutor->alumnosConEstancia()->get();

        return response()->json($alumnos);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TutorEgibide $tutorEgibide) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TutorEgibide $tutorEgibide) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TutorEgibide $tutorEgibide) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TutorEgibide $tutorEgibide) {
        //
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
            'tipo' => $user->tipo,
        ]);
    }

    /**
 * Guardar o actualizar horario y calendario de una estancia.
 */
    /**
 * Guardar o actualizar horario y calendario de una estancia.
 */
public function horasperiodo(Request $request)
{
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
