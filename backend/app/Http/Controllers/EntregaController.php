<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entrega;
use App\Models\EntregaCuaderno;
use App\Models\Ciclos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EntregaController extends Controller
{
    public function mias(Request $request)
    {
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
            ->get(['id', 'archivo', 'fecha', 'entrega_cuaderno_id']);

        return response()->json($entregas);
    }

    /**
     * Devuelve pendientes + realizadas para el alumno.
     * Pendientes = entregas_cuaderno del ciclo del alumno que aún no ha subido.
     */
    public function misEntregasCuaderno(Request $request)
    {
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

        // ✅ Ciclo real del alumno por su grupo (alumnos.grupo -> ciclos.grupo)
        $ciclo = Ciclos::where('grupo', $alumno->grupo)->first();

        if (!$ciclo) {
            return response()->json(['message' => 'No se pudo determinar tu ciclo'], 500);
        }

        $cicloId = $ciclo->id;

        // Entregas asignadas por el tutor para ese ciclo
        $asignadas = EntregaCuaderno::where('ciclo_id', $cicloId)
            ->orderBy('fecha_limite')
            ->get(['id', 'titulo', 'descripcion', 'fecha_limite', 'ciclo_id']);

        // Entregas ya subidas por el alumno (las del cuaderno)
        $realizadas = $cuaderno->entregas()
            ->whereNotNull('entrega_cuaderno_id')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get(['id', 'archivo', 'fecha', 'entrega_cuaderno_id']);

        $hechasIds = $realizadas->pluck('entrega_cuaderno_id')->filter()->values();

        $pendientes = $asignadas->whereNotIn('id', $hechasIds)->values();

        return response()->json([
            'pendientes' => $pendientes,
            'realizadas' => $realizadas,
        ]);
    }

    public function archivo(Request $request, \App\Models\Entrega $entrega)
    {
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

    public function destroy(Request $request, $id)
    {
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

        // (Mejorable: ownership completo). Por ahora mantiene tu lógica.
        $entrega = DB::table('entregas')->where('id', $id)->first();

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }

        if ($entrega->archivo && Storage::disk('public')->exists($entrega->archivo)) {
            Storage::disk('public')->delete($entrega->archivo);
        }

        DB::table('entregas')->where('id', $id)->delete();

        return response()->json(['message' => 'Entrega eliminada correctamente']);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($user->role !== 'alumno') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'entrega_cuaderno_id' => ['required', 'integer', 'exists:entregas_cuaderno,id'],
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

        $entregaCuadernoId = (int) $request->input('entrega_cuaderno_id');

        // ✅ Ciclo real del alumno por su grupo (alumnos.grupo -> ciclos.grupo)
        $ciclo = Ciclos::where('grupo', $alumno->grupo)->first();

        if (!$ciclo) {
            return response()->json(['message' => 'No se pudo determinar tu ciclo'], 500);
        }

        $cicloId = $ciclo->id;

        // Validar que esa entrega corresponde a su ciclo
        $asignada = EntregaCuaderno::where('id', $entregaCuadernoId)
            ->where('ciclo_id', $cicloId)
            ->first();

        if (!$asignada) {
            return response()->json(['message' => 'Esa entrega no pertenece a tu ciclo'], 403);
        }

        // Evitar duplicados
        $yaSubida = $cuaderno->entregas()
            ->where('entrega_cuaderno_id', $entregaCuadernoId)
            ->exists();

        if ($yaSubida) {
            return response()->json(['message' => 'Ya has subido esta entrega'], 409);
        }

        $file = $request->file('archivo');
        $path = $file->store("entregas/cuaderno_{$cuaderno->id}", "public");

        $entrega = Entrega::create([
            'archivo' => $path,
            'fecha' => now()->toDateString(),
            'cuaderno_practicas_id' => $cuaderno->id,
            'entrega_cuaderno_id' => $entregaCuadernoId,
        ]);

        return response()->json(
            $entrega->only(['id', 'archivo', 'fecha', 'entrega_cuaderno_id']),
            201
        );
    }
}
