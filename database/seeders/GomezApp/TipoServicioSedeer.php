<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class TipoServicioSedeer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('servicios')->insert([
            'servicio' => 'Queja',
        ]);
        DB::connection('mysql_gomezapp')->table('servicios')->insert([
            'servicio' => 'Informacion',
        ]);

        DB::connection('mysql_gomezapp')->table('servicios')->insert([
            'servicio' => 'Solicitudes',
        ]);
        
        DB::connection('mysql_gomezapp')->table('servicios')->insert([
            'servicio' => 'Audiencia Publica',
        ]);
    }
}
