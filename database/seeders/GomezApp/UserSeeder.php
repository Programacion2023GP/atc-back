<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'atc@gmail.com',
            'password' => Hash::make('123'),
            'role_id' => 2,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'ecologia@gmail.com',
            'password' => Hash::make('123'),
            'role_id' => 4,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'bacheo@gmail.com',
            'password' => Hash::make('123'),
            'role_id' => 4,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'basura@gmail.com',
            'password' => Hash::make('123'),
            'role_id' => 4,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('desarrollo'),
            'role_id' => 1,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'sp@gmail.com',
            'password' => Hash::make('654321'),
            'role_id' => 5,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'mayte.gomez@gomezpalacio.gob.mx',
            'password' => Hash::make('123456'),
            'role_id' => 6,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'adriana.ornelas@gomezpalacio.gob.mx',
            'password' => Hash::make('123456'),
            'role_id' => 6,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'miguel.martinez@gomezpalacio.gob.mx',
            'password' => Hash::make('123456'),
            'role_id' => 6,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'cristina.alba@gomezpalacio.gob.mx',
            'password' => Hash::make('123456'),
            'role_id' => 6,
            'created_at' => now()
        ]);
        DB::connection('mysql_gomezapp')->table('users')->insert([
            'email' => 'veronica.vanegas@gomezpalacio.gob.mx',
            'password' => Hash::make('654321'),
            'role_id' => 7,
            'created_at' => now()
        ]);
    }
}
