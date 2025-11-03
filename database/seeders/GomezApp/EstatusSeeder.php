<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('estatus')->insert([
            'estatus' => 'ALTA'
        ]);
        DB::connection('mysql_gomezapp')->table('estatus')->insert([
            'estatus' => 'EN TRAMITE'
        ]);
        DB::connection('mysql_gomezapp')->table('estatus')->insert([
            'estatus' => 'NO PROCEDE'
        ]);
        DB::connection('mysql_gomezapp')->table('estatus')->insert([
            'estatus' => 'TERMINADO'
        ]);
    }
}
