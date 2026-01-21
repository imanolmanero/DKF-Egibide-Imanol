<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CiclosController;
use App\Http\Controllers\CompetenciasController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\AlumnosController;
use App\Http\Controllers\FamiliaProfesionalController;
use App\Http\Controllers\NotasController;
use App\Http\Controllers\TutorEgibideController;
use App\Http\Controllers\TutorEmpresaController;
use App\Http\Controllers\SeguimientosController;
use App\Http\Controllers\EntregaController;

Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/entregas/{entrega}/archivo', [EntregaController::class, 'archivo']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Familias Profesionales
    Route::get('/familiasProfesionales', [FamiliaProfesionalController::class, 'index']);

    // Ciclos
    Route::get('/ciclos', [CiclosController::class, 'index']);
    Route::post('/ciclos', [CiclosController::class, 'store']);

    // Competencias
    Route::get('/competencias', [CompetenciasController::class, 'index']);
    Route::get('/competenciasTecnicas/alumno/{alumno_id}', [CompetenciasController::class, 'getCompetenciasTecnicasByAlumno']);
    Route::get('/competenciasTecnicas/alumno/{alumno_id}/asignadas', [CompetenciasController::class, 'getCompetenciasTecnicasAsignadasByAlumno']);
    Route::get('/competenciasTransversales/alumno/{alumno_id}', [CompetenciasController::class, 'getCompetenciasTransversalesByAlumno']);
    Route::get('/competenciasTecnicas/calificaciones/{alumno_id}', [CompetenciasController::class, 'getCalificacionesCompetenciasTecnicas']);
    Route::get('/competenciasTransversales/calificaciones/{alumno_id}', [CompetenciasController::class, 'getCalificacionesCompetenciasTransversales']);

    Route::post('/competenciasTransversales/calificar', [CompetenciasController::class, 'storeCompetenciasTransversalesCalificadas']);
    Route::post('/competencia/tecnica', [CompetenciasController::class, 'storeTecnica']);
    Route::post('/competencia/transversal', [CompetenciasController::class, 'storeTransversal']);
    Route::post('/competenciasTecnicas/asignar', [CompetenciasController::class, 'storeCompetenciasTecnicasAsignadas']);
    Route::post('/competenciasTecnicas/calificar', [CompetenciasController::class, 'storeCompetenciasTecnicasCalificadas']);

    // Notas
    Route::get('/notas/alumno/{alumno_id}/tecnicas', [NotasController::class, 'obtenerNotasTecnicas']);
    Route::get('/notas/alumno/{alumno_id}/transversal', [NotasController::class, 'obtenerNotasTransversales']);

    // Empresas
    Route::get('/empresas', [EmpresasController::class, 'index']);
    Route::post('/empresas', [EmpresasController::class, 'store']);
    Route::get('/me/empresa', [EmpresasController::class, 'miEmpresa']);
    Route::post('/empresas/asignar', [EmpresasController::class, 'storeEmpresaAsignada']);

    // Alumnos
    Route::get('/me/inicio', [AlumnosController::class, 'inicio']);
    Route::get('/alumnos', [AlumnosController::class, 'index']);
    Route::post('/alumnos', [AlumnosController::class, 'store']);
    Route::get('/me/alumno', [AlumnosController::class, 'me']);
    Route::get('/me/nota-cuaderno', [AlumnosController::class, 'notaCuadernoLogeado']);
    Route::get('/alumnos/{alumno_id}/asignaturas', [AlumnosController::class, 'getAsignaturasAlumno']);
    Route::get('/alumnos/{alumno}/entregas', [AlumnosController::class, 'entregas']);

    //Entregas
    Route::get('/entregas/mias', [EntregaController::class, 'mias']);
    Route::post('/entregas', [EntregaController::class, 'store']);

    // Tutor Egibide
    Route::get('/tutorEgibide/{tutorId}/alumnos', [TutorEgibideController::class, 'getAlumnosByCurrentTutor']);
    Route::get('/me/tutor-egibide', [TutorEgibideController::class, 'me']);
    Route::post('/horasperiodo', [TutorEgibideController::class, 'horasperiodo']);

    // Tutor Empresa
    Route::get('/tutorEmpresa/{tutorId}/alumnos', [TutorEmpresaController::class, 'getAlumnosByCurrentInstructor']);
    Route::get('/me/tutor-empresa', [TutorEmpresaController::class, 'me']);

    // Seguimientos
    Route::get('/seguimientos', [SeguimientosController::class, 'index']);
}
);
