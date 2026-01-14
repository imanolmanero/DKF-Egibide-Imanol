<?php

namespace App\Http\Controllers;

use App\Models\Ciclos;
use Illuminate\Http\Request;

class CiclosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Ciclos::all(); // Obtener todos los ciclos
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Ciclos $ciclos) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ciclos $ciclos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ciclos $ciclos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ciclos $ciclos)
    {
        //
    }
}
