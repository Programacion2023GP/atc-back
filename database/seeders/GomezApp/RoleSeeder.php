<?php

namespace Database\Seeders\GomezApp;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql_gomezapp')->table('roles')->insert([
            'role' => 'SuperAdmin',
            'description' => 'Rol dedicado para la completa configuraciond del sistema desde el area de desarrollo.',
            'read' => 'todas',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'more_permissions' => 'todas',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('roles')->insert([
            'role' => 'Administrador',
            'description' => 'Rol dedicado para usuarios que gestionaran el sistema.',
            'read' => '1,2,3,4,5,6,7,14,15,16',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'more_permissions' => 'todas',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('roles')->insert([
            'role' => 'Ciudadano',
            'description' => 'Rol dedicado para usuarios que harán se registran desde la AppMovil para levantar reportes.',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('roles')->insert([
            'role' => 'Encargado',
            'description' => 'Rol dedicado para usuarios que gestionaran el sistema.',
            'read' => '3,4,17,18',
            'create' => '',
            'update' => 'todas',
            'delete' => 'todas',
            'more_permissions' => 'responder,ver,evidencia',
            'created_at' => now(),
        ]);

        DB::connection('mysql_gomezapp')->table('roles')->insert([
            'role' => 'SecretariaP',
            'description' => 'Rol dedicado para usuarios que Secretaria Particular.',
            'read' => '1,5,8,12,13,14,15,17,18,19,20,21',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'more_permissions' => 'ver,editar,eliminar,papeleria',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('roles')->insert([
            'role' => 'CapturaSP',
            'description' => 'Rol dedicado para usuarios que Secretaria Particular.',
            'read' => '17,18',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'more_permissions' => 'ver,editar,papeleria',
            'created_at' => now(),
        ]);
        DB::connection('mysql_gomezapp')->table('roles')->insert([
            'role' => 'EncargadaSP',
            'description' => 'Rol dedicado para usuarios que Secretaria Particular.',
            'read' => '8,12,13,14,15,17,18,19',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'more_permissions' => 'ver,editar,eliminar,papeleria',
            'created_at' => now(),
        ]);
    }
}
