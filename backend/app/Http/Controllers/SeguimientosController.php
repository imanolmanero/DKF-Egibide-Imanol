<?php

namespace App\Http\Controllers;

use App\Models\Seguimientos;
use Illuminate\Http\Request;

class SeguimientosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Seguimientos::all();
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
    public function show(Seguimientos $seguimientos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seguimientos $seguimientos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seguimientos $seguimientos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seguimientos $seguimientos)
    {
        //
    }
}
