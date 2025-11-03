<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class OrigenReporteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('origen_reporte')->insert([
            'origen' => 'Modulo',
        ]);
        DB::connection('mysql_gomezapp')->table('origen_reporte')->insert([
            'origen' => 'Telefono',
        ]);
        DB::connection('mysql_gomezapp')->table('origen_reporte')->insert([
            'origen' => 'APP',
        ]);
    }
}
