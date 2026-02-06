<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ImportacionService;
use Exception;
class ImportacionController extends Controller
{
    protected $importService;

    public function __construct(ImportacionService $importService) {
        $this->importService = $importService;
    }

    public function upload(Request $request) {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls'
        ]);

        try {
            $resultado = $this->importService->importar($request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Importación realizada con éxito',
                'data' => $resultado
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 422);
        }
    }
}
