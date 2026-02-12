<?php

namespace App\Http\Controllers;

use App\Models\EntregaCuaderno;
use App\Models\TutorEgibide;
use Illuminate\Http\Request;


class EntregaCuadernoController extends Controller
{
    private function requireTutorEgibide(Request $request): TutorEgibide
    {
        $user = $request->user();

        if (!$user || $user->role !== 'tutor_egibide') {
            abort(403, 'Solo un tutor de Egibide puede crear entregas de cuaderno.');
        }

        $tutor = TutorEgibide::where('user_id', $user->id)->first();
        if (!$tutor) {
            abort(403, 'No existe un tutor Egibide asociado a este usuario.');
        }

        return $tutor;
    }

    public function index(Request $request)
    {
        $tutor = $this->requireTutorEgibide($request);

        return EntregaCuaderno::with('ciclo')
            ->where('tutor_id', $tutor->id)
            ->orderByDesc('fecha_limite')
            ->get();
    }

    public function store(Request $request)
    {
        $tutor = $this->requireTutorEgibide($request);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'fecha_limite' => ['required', 'date'],
            'ciclo_id' => ['required', 'integer', 'exists:ciclos,id'],
        ]);

        $entrega = EntregaCuaderno::create([
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'fecha_limite' => $data['fecha_limite'],
            'ciclo_id' => $data['ciclo_id'],
            'tutor_id' => $tutor->id,
        ]);

        return response()->json($entrega, 201);
    }

    public function misEntregasCuaderno(Request $request) {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'No autenticado'], 401);
        if ($user->role !== 'alumno') return response()->json(['message' => 'No autorizado'], 403);

        $alumno = $user->alumno;
        if (!$alumno) return response()->json(['message' => 'No existe alumno asociado a este usuario'], 404);

        $estancia = $alumno->estancias()
            ->orderByRaw('fecha_fin IS NULL DESC')
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$estancia) return response()->json(['message' => 'No tienes estancia asignada'], 404);

        $cuaderno = $estancia->cuadernoPracticas;
        if (!$cuaderno) return response()->json(['message' => 'No tienes cuaderno de prácticas todavía'], 404);

        // 🔑 Aquí necesitas el ciclo del alumno/estancia/cuaderno (ajusta el campo)
        // Te dejo las 3 opciones típicas; usa la que exista en tu BD:
        // $cicloId = $alumno->ciclo_id;
        // $cicloId = $estancia->ciclo_id;
        // $cicloId = $cuaderno->ciclo_id;

        $cicloId = $estancia->ciclo_id; // <-- AJUSTA ESTA LÍNEA A TU MODELO REAL

        $asignadas = EntregaCuaderno::where('ciclo_id', $cicloId)
            ->orderBy('fecha_limite')
            ->get(['id','titulo','descripcion','fecha_limite','ciclo_id']);

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
}
