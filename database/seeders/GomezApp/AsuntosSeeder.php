<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AsuntosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('asuntos')->insert([
            'asunto' => 'BACHEO',
            'bg_circle' => 'blue',
            'bg_card' => '#1F2227',
            'icono' => 'gomezapp/icons/btbacheo.png',
            'app' => 1,
            'letter_black' => 1,
            'active' => 1,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('asuntos')->insert([
            'asunto' => 'BASURA',
            'bg_circle' => 'red',
            'bg_card' => '#1F2227',
            'icono' => 'gomezapp/icons/btbasura.png',
            'app' => 1,
            'letter_black' => 1,
            'active' => 1,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('asuntos')->insert([
            'asunto' => 'ECOLOGIA',
            'bg_circle' => 'green',
            'bg_card' => '#1F2227',
            'icono' => 'gomezapp/icons/btecologia.png',
            'app' => 1,
            'letter_black' => 1,
            'active' => 1,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('asuntos')->insert([
            'asunto' => 'ALUMBRADO PUBLICO',
            'bg_circle' => 'gold',
            'bg_card' => '#1F2227',
            'icono' => 'gomezapp/icons/btalumbrado.png',
            'app' => 1,
            'letter_black' => 1,
            'active' => 1,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('asuntos')->insert([
            'asunto' => 'VIGILANCIA',
            'bg_circle' => 'violet',
            'bg_card' => '#1F2227',
            'icono' => 'gomezapp/icons/btvigilancia.png',
            'app' => 1,
            'letter_black' => 1,
            'active' => 1,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('asuntos')->insert([
            'asunto' => 'OTRO',
            'bg_circle' => 'grey',
            'bg_card' => '#1F2227',
            'icono' => 'gomezapp/icons/btotros.png',
            'app' => 1,
            'letter_black' => 1,
            'active' => 1,
            'created_at' => now(),
        ]);
    }
}
