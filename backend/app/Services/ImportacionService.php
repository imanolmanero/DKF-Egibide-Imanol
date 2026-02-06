<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory; // Asegúrate de tener esta librería
use Exception;

class ImportacionService {

    public function importar(UploadedFile $file): array {
        $extension = $file->getClientOriginalExtension();
        $data = [];
        $header = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Lógica para EXCEL
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $header = array_shift($rows);
            foreach ($rows as $row) {
                if (isset($row[0]) && !empty($row[0])) {
                    // Combinamos encabezado con fila para tener array asociativo
                    $data[] = array_combine($header, $row);
                }
            }
        } else {
            // Lógica para CSV (la que ya tenías corregida con UTF-8)
            $content = file_get_contents($file->getRealPath());
            $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }
            $delimiter = str_contains($content, ';') ? ';' : ',';
            $lines = explode("\n", str_replace("\r", "", trim($content)));
            $header = str_getcsv(array_shift($lines), $delimiter);
            $header[0] = preg_replace('/^[\xEF\xBB\xBF\xFF\xFE]/', '', $header[0]);

            foreach ($lines as $line) {
                $fields = str_getcsv($line, $delimiter);
                if (count($header) == count($fields)) {
                    $data[] = array_combine($header, $fields);
                }
            }
        }

        return DB::transaction(function () use ($data, $header) {
            // Detectar por columnas (Egibide usa nombres específicos)
            if (in_array('DNI ALUMNO', $header)) {
                return $this->procesarAlumnos($data);
            } 
            if (in_array('Alias_Profesor', $header)) {
                return $this->procesarEstructuraYProfesores($data);
            }
            throw new Exception("Formato de columnas no reconocido.");
        });
    }



    private function procesarAlumnos(array $data): array {
    $cont = 0;
    foreach ($data as $row) {
        // 1. Asegurar el Usuario primero
        $user = User::updateOrCreate(
            ['email' => $row['EMAIL ALUMNO']],
            [
                'password' => Hash::make($row['DNI ALUMNO']),
                'role' => 'alumno'
            ]
        );

        // 2. BUSQUEDA MANUAL (Más segura para evitar el error de Integrity Constraint)
        $dni = trim($row['DNI ALUMNO']);
        
        // Verificamos si ya existe el alumno por DNI
        $existeAlumno = DB::table('alumnos')->where('dni', $dni)->first();

        if ($existeAlumno) {
            // Si existe, actualizamos usando su ID único
            DB::table('alumnos')->where('id', $existeAlumno->id)->update([
                'user_id'      => $user->id,
                'nombre'       => $row['NOMBRE ALUMNO'],
                'apellidos'    => $row['APELLIDO1 ALUMNO'] . ' ' . $row['APELLIDO2 ALUMNO'],
                'matricula_id' => $row['MATRICULA ALUMNO'],
                'updated_at'   => now()
            ]);
        } else {
            // Si no existe, insertamos de cero
            DB::table('alumnos')->insert([
                'user_id'      => $user->id,
                'dni'          => $dni,
                'nombre'       => $row['NOMBRE ALUMNO'],
                'apellidos'    => $row['APELLIDO1 ALUMNO'] . ' ' . $row['APELLIDO2 ALUMNO'],
                'matricula_id' => $row['MATRICULA ALUMNO'],
                'created_at'   => now(),
                'updated_at'   => now()
            ]);
        }
        $cont++;
    }
    return ['tipo' => 'Alumnos', 'cantidad' => $cont];
}

    private function procesarEstructuraYProfesores(array $data): array {
        $contProf = 0;
        $contAsig = 0;

        foreach ($data as $row) {
            // 1. Usuario Profesor
            $email = strtolower($row['Alias_Profesor']) . "@egibide.org";
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password' => Hash::make('egibide2025'), 
                    'role' => 'tutor_egibide'
                ]
            );

            // 2. Perfil Tutor
            DB::table('tutores')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'nombre' => $row['Nombre'],
                    'apellidos' => $row['Apel1'] . ' ' . $row['Apel2'],
                    'alias' => $row['Alias_Profesor'],
                    'updated_at' => now()
                ]
            );

            // 3. Asignatura vinculada al Ciclo
            $cicloId = DB::table('ciclos')->where('codigo', $row['Grupo'])->value('id');
            if ($cicloId) {
                DB::table('asignaturas')->updateOrInsert(
                    ['nombre_asignatura' => $row['Des_Asig'], 'ciclo_id' => $cicloId],
                    [
                        'codigo_asignatura' => $row['Grupo'] . "-" . substr(md5($row['Des_Asig']), 0, 4), 
                        'updated_at' => now()
                    ]
                );
                $contAsig++;
            }
            $contProf++;
        }
        return ['profesores' => $contProf, 'asignaturas' => $contAsig];
    }
}