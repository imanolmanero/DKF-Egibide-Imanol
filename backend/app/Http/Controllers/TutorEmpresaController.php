<?php

namespace App\Http\Controllers;

use App\Models\Estancia;
use App\Models\TutorEmpresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class TutorEmpresaController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return TutorEmpresa::with('empresa')->get();
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
        return $tutorEmpresa->load('empresa');
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

    /**
     * Crear un nuevo instructor y asignarlo automáticamente a una empresa
     * ACTUALIZADO: Desasigna cualquier instructor anterior de la empresa
     */
    public function crearInstructor(Request $request)
    {
        // Log para debugging
        Log::info('Creando instructor', ['request_data' => $request->all()]);

        try {
            $validated = $request->validate([
                'empresa_id' => 'required|exists:empresas,id',
                'nombre' => 'required|string|max:100',
                'apellidos' => 'required|string|max:150',
                'email' => 'required|email|unique:users,email',
                'telefono' => 'nullable|string|max:20',
                'ciudad' => 'nullable|string|max:120',
                'password' => 'required|string|min:8',
            ]);

            Log::info('Validación exitosa', ['validated' => $validated]);

        } catch (ValidationException $e) {
            Log::error('Error de validación al crear instructor', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Desasignar cualquier instructor anterior de esta empresa
            TutorEmpresa::where('empresa_id', $validated['empresa_id'])
                ->update(['empresa_id' => null]);

            Log::info('Instructores anteriores desasignados', ['empresa_id' => $validated['empresa_id']]);

            // 2. Crear usuario
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'instructor',
                'email_verified_at' => now(),
            ]);

            Log::info('Usuario creado', ['user_id' => $user->id, 'email' => $user->email]);

            // 3. Crear instructor
            $instructor = TutorEmpresa::create([
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'telefono' => $validated['telefono'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'empresa_id' => $validated['empresa_id'],
                'user_id' => $user->id,
            ]);

            Log::info('Instructor creado', ['instructor_id' => $instructor->id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instructor creado y asignado correctamente. Cualquier instructor anterior ha sido desasignado.',
                'instructor' => $instructor->load('empresa'),
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            
            // Log detallado del error
            Log::error('Error al crear instructor', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el instructor: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Obtener todos los instructores disponibles (para asignar)
     */
    public function getInstructoresDisponibles()
    {
        // Obtener todos los instructores con su empresa actual
        $instructores = TutorEmpresa::with('empresa', 'usuario')->get();

        return response()->json($instructores);
    }

    /**
     * Asignar un instructor existente a una empresa
     * ACTUALIZADO: Desasigna cualquier instructor anterior de la empresa
     */
    public function asignarInstructor(Request $request)
    {
        // Log para debugging
        Log::info('Asignando instructor', ['request_data' => $request->all()]);

        try {
            $validated = $request->validate([
                'instructor_id' => 'required|exists:instructores,id',
                'empresa_id' => 'required|exists:empresas,id',
            ]);

            Log::info('Validación exitosa', ['validated' => $validated]);

        } catch (ValidationException $e) {
            Log::error('Error de validación al asignar instructor', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Desasignar cualquier instructor anterior de esta empresa
            TutorEmpresa::where('empresa_id', $validated['empresa_id'])
                ->update(['empresa_id' => null]);

            Log::info('Instructores anteriores desasignados', ['empresa_id' => $validated['empresa_id']]);

            // 2. Asignar el nuevo instructor
            $instructor = TutorEmpresa::findOrFail($validated['instructor_id']);
            $instructor->empresa_id = $validated['empresa_id'];
            $instructor->save();

            Log::info('Instructor asignado', [
                'instructor_id' => $instructor->id,
                'empresa_id' => $validated['empresa_id']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instructor asignado correctamente. Cualquier instructor anterior ha sido desasignado.',
                'instructor' => $instructor->load('empresa'),
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            
            // Log detallado del error
            Log::error('Error al asignar instructor', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el instructor: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}