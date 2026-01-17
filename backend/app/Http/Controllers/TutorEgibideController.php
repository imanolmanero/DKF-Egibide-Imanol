<?php

namespace App\Http\Controllers;

use App\Models\TutorEgibide;
use Illuminate\Http\Request;

class TutorEgibideController extends Controller {
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

    public function getAlumnos() {
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
}
