<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alumnos;
use App\Models\Empresas;
use App\Models\TutorEgibide;
use App\Models\Ciclos;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function inicioAdmin(Request $request)
    {
        $user = $request->user();

        // (Opcional pero recomendado) asegurar rol admin
        if (($user->role ?? null) !== 'admin') {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        }

        return response()->json([
            'admin' => [
                'email' => $user->email,
            ],
            'counts' => [
                'alumnos'  => Alumnos::count(),
                'empresas' => Empresas::count(),
                'tutores'  => TutorEgibide::count(),
                'ciclos'   => Ciclos::count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function detalleEmpresa($empresaId)
{
    $empresa = Empresas::with([
            // instructores por relación belongsToMany (distinct ya lo tienes)
            'instructores:id,nombre,apellidos,telefono,ciudad,empresa_id',
        ])->findOrFail($empresaId);

        return response()->json($empresa);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function detalleAlumno(Request $request, $id){
        $user = $request->user();

        if (($user->role ?? null) !== 'admin') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $alumno = Alumnos::with([
            'estancias.empresa:id,nombre',
        ])->findOrFail($id);

        return response()->json($alumno);
    }

}
