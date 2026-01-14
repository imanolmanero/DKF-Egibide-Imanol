<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CiclosController;
use App\Http\Controllers\CompetenciasController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\AlumnosController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');

Route::get('/ciclos', [CiclosController::class, 'index']);
Route::get('/competencias', [CompetenciasController::class, 'index']);
Route::get('/empresas', [EmpresasController::class, 'index']);
Route::get('/alumnos', [AlumnosController::class, 'index']);


