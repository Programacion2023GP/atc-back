<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserDepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('usuarios_departamentos')->insert([
            'user_id' => 1,
            'departamento_id' => 23,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('usuarios_departamentos')->insert([
            'user_id' => 2, //Ecologia
            'departamento_id' => 4,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('usuarios_departamentos')->insert([
            'user_id' => 3, //Bacheo
            'departamento_id' => 7,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('usuarios_departamentos')->insert([
            'user_id' => 4, //Limpieza
            'departamento_id' => 6,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('usuarios_departamentos')->insert([
            'user_id' => 5, //Admin
            'departamento_id' => 23,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('usuarios_departamentos')->insert([
            'user_id' => 6, //SP
            'departamento_id' => 23,
            'created_at' => now()
        ]);
    
    }
}
