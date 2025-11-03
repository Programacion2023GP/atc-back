<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        #1 - Dashboard
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Dashboard',
            'caption' => '',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 1,
            'created_at' => now(),
        ]);
        #2 - Tablero
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Tablero',
            'type' => 'item',
            'belongs_to' => 1,
            'url' => '/admin',
            'icon' => 'IconDashboard',
            'order' => 1,
            'created_at' => now(),
        ]);
        #3 - Administrativo
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Administrativo',
            'caption' => 'Control administrativo',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 2,
            'created_at' => now(),
        ]);
        #4 - Registros
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'ATC_Solicitud',
            'type' => 'item',
            'belongs_to' => 3,
            'url' => '/admin/registros',
            'icon' => 'IconCheckupList',
            'order' => 1,
            'created_at' => now(),
        ]);
        #5 - Reportes
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Reportes',
            'caption' => 'Gestión de Análisis',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 3,
            'created_at' => now(),
        ]);
        #6 - Resultados por Departamento
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Resultados por Departamento',
            'type' => 'item',
            'belongs_to' => 5,
            'url' => '/admin/resultados-por-departamento',
            'icon' => 'IconCheckupList',
            'order' => 1,
            'created_at' => now(),
        ]);
        #7 - Resultados por Relaciones
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Resultados por Asuntos',
            'type' => 'item',
            'belongs_to' => 5,
            'url' => '/admin/resultados-por-asuntos',
            'icon' => 'IconCheckupList',
            'order' => 2,
            'created_at' => now(),
        ]);
        #8 - Catalogos
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Catalogos',
            'caption' => 'Control de Catalogos',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 3,
            'created_at' => now(),
        ]);
        #9 - Usuarios
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Usuarios',
            'type' => 'item',
            'belongs_to' => 8,
            'url' => '/admin/usuarios',
            'icon' => 'IconUsers',
            'order' => 1,
            'created_at' => now(),
        ]);
        #10 - Roles
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Roles',
            'type' => 'item',
            'belongs_to' => 8,
            'url' => '/admin/roles',
            'icon' => 'IconAB2',
            'order' => 2,
            'created_at' => now(),
        ]);
        #11 - Menus
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Menus',
            'type' => 'item',
            'belongs_to' => 8,
            'url' => '/admin/menus',
            'icon' => 'IconCategory',
            'order' => 3,
            'created_at' => now(),
        ]);
        #12 - Departamentos
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Departamentos',
            'type' => 'item',
            'belongs_to' => 8,
            'url' => '/admin/departamentos',
            'icon' => 'IconBrandCodepen',
            'order' => 3,
            'created_at' => now(),
        ]);
        #13 - Asuntos
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Asuntos',
            'type' => 'item',
            'belongs_to' => 8,
            'url' => '/admin/asuntos',
            'icon' => 'IconBrandTrello',
            'order' => 3,
            'created_at' => now(),
        ]);
        #14 - Relaciones
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Relaciones',
            'type' => 'group',
            'belongs_to' => 0,
            'url' => '/admin/relaciones',
            // 'icon' => 'IconPaperBag',
            'order' => 3,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Dep-Asuntos',
            'type' => 'item',
            'belongs_to' => 14,
            'url' => '/admin/dep-asuntos',
            'icon' => 'IconHierarchy2',
            'order' => 3,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Usuarios-Dep',
            'type' => 'item',
            'belongs_to' => 14,
            'url' => '/admin/usuarios-dep',
            'icon' => 'IconHierarchy2',
            'order' => 3,
            'created_at' => now(),
        ]);

        #15 - Secretaria Particular
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Secretaria Particular',
            'type' => 'group',
            'belongs_to' => 0,
            'url' => '/admin/secpar/solicitud',
            // 'icon' => 'IconPaperBag',
            'order' => 4,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'SP_Solicitud',
            'type' => 'item',
            'belongs_to' => 17,
            'url' => '/admin/secpar/solicitud',
            'icon' => 'IconHierarchy2',
            'order' => 4,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Colonias',
            'type' => 'item',
            'belongs_to' => 17,
            'url' => '/admin/secpar/colonias',
            'icon' => 'IconHierarchy2',
            'order' => 4,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'Incumplimiento',
            'type' => 'item',
            'belongs_to' => 5,
            'url' => '/admin/secpar/reporte/incum',
            'icon' => 'IconCheckupList',
            'order' => 3,
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('menus')->insert([
            'menu' => 'SP_Estadísticas',
            'type' => 'item',
            'belongs_to' => 1,
            'url' => '/admin/secpar/dashboard',
            'icon' => 'IconCheckupList',
            'order' => 2,
            'created_at' => now(),
        ]);
    }
}
