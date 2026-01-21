<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entrega;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class EntregaController extends Controller
{
     public function mias(Request $request){
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($user->role !== 'alumno') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $alumno = $user->alumno;

        if (!$alumno) {
            return response()->json(['message' => 'No existe alumno asociado a este usuario'], 404);
        }

        $estancia = $alumno->estancias()
            ->orderByRaw('fecha_fin IS NULL DESC')
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$estancia) {
            return response()->json(['message' => 'No tienes estancia asignada'], 404);
        }

        $cuaderno = $estancia->cuadernoPracticas;

        if (!$cuaderno) {
            return response()->json(['message' => 'No tienes cuaderno de prácticas todavía'], 404);
        }

        $entregas = $cuaderno->entregas()
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get(['id', 'archivo', 'fecha']);

        return response()->json($entregas);
    }

    public function archivo(Request $request, \App\Models\Entrega $entrega){
        $path = ltrim((string) $entrega->archivo, '/');

        if (!Storage::disk('public')->exists($path)) {

            $alt = 'entregas/' . $path;

            if (Storage::disk('public')->exists($alt)) {
                $path = $alt;
            } else {
                return response()->json(['message' => 'Archivo no encontrado'], 404);
            }
        }

        $absolutePath = Storage::disk('public')->path($path);

        return response()->download($absolutePath, basename($path));
    }   

    public function store(Request $request){
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($user->role !== 'alumno') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB
        ]);

        $alumno = $user->alumno;

        if (!$alumno) {
            return response()->json(['message' => 'No existe alumno asociado a este usuario'], 404);
        }

        $estancia = $alumno->estancias()
            ->orderByRaw('fecha_fin IS NULL DESC')
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$estancia) {
            return response()->json(['message' => 'No tienes estancia asignada'], 404);
        }

        $cuaderno = $estancia->cuadernoPracticas;

        if (!$cuaderno) {
            return response()->json(['message' => 'No tienes cuaderno de prácticas todavía'], 404);
        }

        $file = $request->file('archivo');

        $path = $file->store("entregas/cuaderno_{$cuaderno->id}", "public");

        $entrega = Entrega::create([
            'archivo' => $path,
            'fecha' => now()->toDateString(),
            'cuaderno_practicas_id' => $cuaderno->id,
        ]);

        return response()->json($entrega->only(['id', 'archivo', 'fecha']), 201);
    }
}
