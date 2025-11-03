<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DepAsuntoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('departamentos_asuntos')->insert([
            'department_id' => '7',
            'asunto_id' => '1',
        ]);
        DB::connection('mysql_gomezapp')->table('departamentos_asuntos')->insert([
            'department_id' => '6',
            'asunto_id' => '2',
        ]);
        DB::connection('mysql_gomezapp')->table('departamentos_asuntos')->insert([
            'department_id' => '4',
            'asunto_id' => '3',
        ]);
        DB::connection('mysql_gomezapp')->table('departamentos_asuntos')->insert([
            'department_id' => '2',
            'asunto_id' => '4',
        ]);
        DB::connection('mysql_gomezapp')->table('departamentos_asuntos')->insert([
            'department_id' => '20',
            'asunto_id' => '5',
        ]);
        DB::connection('mysql_gomezapp')->table('departamentos_asuntos')->insert([
            'department_id' => '1',
            'asunto_id' => '6',
        ]);
    }
}
