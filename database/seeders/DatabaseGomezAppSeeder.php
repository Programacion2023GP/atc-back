<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseGomezAppSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            GomezApp\MenuSeeder::class,
            GomezApp\RoleSeeder::class,
            GomezApp\DepartamentSeeder::class,
            GomezApp\UserSeeder::class,
            GomezApp\UserDepSeeder::class,
            GomezApp\TipoServicioSedeer::class,
            GomezApp\OrigenReporteSeeder::class,
            GomezApp\AsuntosSeeder::class,
            GomezApp\DepAsuntoSeeder::class,
            GomezApp\EstatusSeeder::class,

        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
