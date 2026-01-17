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

    public function getAlumnosByInstructor($instructorId) {
        $alumnos = TutorEmpresa::findOrFail($instructorId)
            ->alumnosConEstancia()
            ->get();

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

    public function me(Request $request)
    {
        $user = $request->user();

        $tutor = TutorEmpresa::where('user_id', $user->id)->first();

        return response()->json([
            'id' => $tutor->id,
            'nombre' => $tutor->nombre,
            'apellidos' => $tutor->apellidos,
            'email' => $user->email,
            'tipo' => $user->tipo,
        ]);
    }
}
