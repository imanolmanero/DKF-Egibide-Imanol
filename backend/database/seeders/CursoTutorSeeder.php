<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoTutorSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        DB::table('curso_tutor')->updateOrInsert(
            [
                'curso_id' => 1,
                'tutor_id' => 1
            ]
        );
    }
}
