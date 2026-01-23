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
use App\Http\Controllers\AdminController;

Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/entregas/{entrega}/archivo', [EntregaController::class, 'archivo']);

Route::middleware('auth:sanctum')->group(
    function () {
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
        Route::get('/notas/alumno/{alumno_id}/egibide', [NotasController::class, 'obtenerNotasEgibide']);
        Route::post('/notas/alumno/egibide/guardar', [NotasController::class, 'guardarNotasEgibide']);
        Route::get('/notas/alumno/{alumno_id}/cuaderno', [NotasController::class, 'obtenerNotaCuadernoByAlumno']);
        Route::post('/notas/alumno/cuaderno/guardar', [NotasController::class, 'guardarNotasCuaderno']);

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
        Route::get('/alumnos/{alumno_id}/asignaturas', [AlumnosController::class, 'getAsignaturasAlumno']);
        Route::get('/alumnos/{alumno}/entregas', [AlumnosController::class, 'entregas']);

        //Entregas
        Route::get('/entregas/mias', [EntregaController::class, 'mias']);
        Route::post('/entregas', [EntregaController::class, 'store']);
        Route::delete('/entregas/{id}', [EntregaController::class, 'destroy']);

        // Tutor Egibide
        Route::get('/tutorEgibide/inicio', [TutorEgibideController::class, 'inicioTutor']);
        Route::get('/tutorEgibide/{tutorId}/alumnos', [TutorEgibideController::class, 'getAlumnosByCurrentTutor']);
        Route::get('/tutorEgibide/{tutorId}/empresas', [TutorEgibideController::class, 'conseguirEmpresasporTutor']);
        Route::get('/tutorEgibide/empresa/{empresaId}', [TutorEgibideController::class, 'getDetalleEmpresa']);
        Route::get('/me/tutor-egibide', [TutorEgibideController::class, 'me']);
        Route::post('/horasperiodo', [TutorEgibideController::class, 'horasperiodo']);

        // Tutor Empresa
        Route::get('/tutorEmpresa/inicio', [TutorEmpresaController::class, 'inicioInstructor']);
        Route::get('/tutorEmpresa/{tutorId}/alumnos', [TutorEmpresaController::class, 'getAlumnosByCurrentInstructor']);
        Route::get('/me/tutor-empresa', [TutorEmpresaController::class, 'me']);

        // Seguimientos
        Route::get('/seguimientos/alumno/{alumno_Id}', [SeguimientosController::class, 'seguimientosAlumno']);
        Route::post('/nuevo-seguimiento', [SeguimientosController::class, 'nuevoSeguimiento']);
        Route::delete('/seguimientos/{seguimiento}', [SeguimientosController::class, 'destroy']);

        //Admin
        Route::get('/admin/inicio', [AdminController::class, 'inicioAdmin']);
        Route::get('admin/ciclos/{ciclo}', [CiclosController::class, 'show']);
    }
);
