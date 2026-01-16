<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CiclosController;
use App\Http\Controllers\CompetenciasController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\AlumnosController;
use App\Http\Controllers\FamiliaProfesionalController;
use App\Http\Controllers\TutorEgibideController;
use App\Http\Controllers\TutorEmpresaController;
use App\Http\Controllers\SeguimientosController;

Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');

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
    Route::post('/competencia/tecnica', [CompetenciasController::class, 'storeTecnica']);
    Route::post('/competencia/transversal', [CompetenciasController::class, 'storeTransversal']);

    // Empresas
    Route::get('/empresas', [EmpresasController::class, 'index']);
    Route::post('/empresas', [EmpresasController::class, 'store']);
    Route::get('/me/empresa', [EmpresasController::class, 'miEmpresa']);

    // Alumnos
    Route::get('/alumnos', [AlumnosController::class, 'index']);
    Route::post('/alumnos', [AlumnosController::class, 'store']);
    Route::get('/me/alumno', [AlumnosController::class, 'me']);

    // Tutor Egibide
    Route::get('/tutorEgibide/{tutorId}/alumnos', [TutorEgibideController::class, 'getAlumnos']);

    // Tutor Empresa
    Route::get('/tutorEmpresa/{tutorId}/alumnos', [TutorEmpresaController::class, 'getAlumnosByInstructor']);

    // Seguimientos
    Route::get('/seguimientos', [SeguimientosController::class, 'index']);
});
