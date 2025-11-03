<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class DepartamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'No Aplica',
            'description' => 'Para asignar a usuarios administrativos.',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'ALUMBRADO PUBLICO',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'CONTROL Y BIENESTAR ANIMAL',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'ECOLOGIA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'PARQUES Y JARDINES',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'LIMPIEZA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'BACHEO',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'DIRECCION DIF MUNICIPAL',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);


        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'SIDEAPA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);


        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'SALUD MUNICIPAL',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'OBRAS PUBLICAS',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'EJECUCION FISCAL',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'INSTITUTO MUNICIPAL DE LA VIVIENDA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'JEFATURA DE EDUCACION',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'SERVICIOS PUBLICOS',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'CIRCULO RECOLECTOR',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'BIENESTAR SOCIAL',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'PLAZAS Y MERCADOS',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'DIRECCION DE PROTECCION CIVIL',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'SEGURIDAD PUBLICA Y VIALIDAD',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'TRANSITO Y VIALIDAD',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'SIDEAPAAR',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'ATENCION CIUDADANA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'DIRECCION JURIDICA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'DESARROLLO RURAL',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'INSTITUTO MUNICIPAL DE LA MUJER',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'INSTITUTO MUNICIPAL DEL DEPORTE',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'SECRETARIA TECNICA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'PROCURADURIA DE LA MUJER',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'FOMENTO ECONÓMICO Y TURÍSTICO',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'INSTITUTO MUNICIPAL DE LA JUVENTUD',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'DIRECTORA DE SISTEMAS TI',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'RECURSOS HUMANOS',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'FOMENTO A LA VIVIENDA',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);


        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'ESTACIONOMETROS',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'DIRECCION DE CATASTRO',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('departments')->insert([
            'department' => 'SUBDIRECTOR DE AUTOTRANSPORTE',
            'description' => 'CargaSeeder',
            'created_at' => now(),
        ]);
    }
}
