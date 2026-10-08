<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CONNECTION = 'mysql_gomezapp';
    private const PERMISSION = 'Ver todas las solicitudes internas';

    public function up()
    {
        $db = DB::connection(self::CONNECTION);
        $menu = $db->table('menus')->where('url', '/admin/solicitudes-internas')->where('type', 'item')->first();
        if (! $menu) return;

        $menuPermissions = collect(explode(',', (string) $menu->others_permissions))
            ->map(fn ($value) => trim($value))->filter()->push(self::PERMISSION)->unique()->implode(',');
        $db->table('menus')->where('id', $menu->id)->update(['others_permissions' => $menuPermissions, 'updated_at' => now()]);

        $db->table('roles')->whereIn('role', ['Asistente Oficialía Mayor', 'Oficial Mayor'])->get()->each(function ($role) use ($db) {
            if ($role->more_permissions === 'todas') return;
            $permissions = collect(explode(',', (string) $role->more_permissions))
                ->map(fn ($value) => trim($value))->filter()->push(self::PERMISSION)->unique()->implode(',');
            $db->table('roles')->where('id', $role->id)->update(['more_permissions' => $permissions, 'updated_at' => now()]);
        });
    }

    public function down()
    {
        $db = DB::connection(self::CONNECTION);
        $remove = fn ($value) => collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))->filter(fn ($item) => $item !== self::PERMISSION)->unique()->implode(',');

        $menu = $db->table('menus')->where('url', '/admin/solicitudes-internas')->where('type', 'item')->first();
        if ($menu) $db->table('menus')->where('id', $menu->id)->update(['others_permissions' => $remove($menu->others_permissions), 'updated_at' => now()]);

        $db->table('roles')->whereIn('role', ['Asistente Oficialía Mayor', 'Oficial Mayor'])->get()->each(function ($role) use ($db, $remove) {
            if ($role->more_permissions !== 'todas') $db->table('roles')->where('id', $role->id)->update(['more_permissions' => $remove($role->more_permissions), 'updated_at' => now()]);
        });
    }
};
