<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $db = DB::connection('mysql_gomezapp');
        $groupId = $db->table('menus')->where('menu', 'Oficialía Mayor')->where('belongs_to', 0)->value('id');
        if (!$groupId) $groupId = $db->table('menus')->insertGetId(['menu' => 'Oficialía Mayor', 'caption' => 'Gestión interna', 'type' => 'group', 'belongs_to' => 0, 'url' => '/admin/solicitudes-internas', 'order' => 5, 'active' => 1, 'show_counter' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $itemId = $db->table('menus')->where('url', '/admin/solicitudes-internas')->where('type', 'item')->value('id');
        if (!$itemId) $itemId = $db->table('menus')->insertGetId(['menu' => 'Solicitudes internas', 'type' => 'item', 'belongs_to' => $groupId, 'url' => '/admin/solicitudes-internas', 'icon' => 'IconFileDescription', 'order' => 1, 'active' => 1, 'show_counter' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $roleIds = $db->table('users')->join('usuarios_departamentos as ud', 'ud.user_id', '=', 'users.id')->distinct()->pluck('users.role_id');
        foreach ($db->table('roles')->whereIn('id', $roleIds)->get() as $role) {
            if ($role->read === 'todas') continue;
            $read = collect(explode(',', (string) $role->read))->filter()->map(fn($id) => (string) $id);
            $db->table('roles')->where('id', $role->id)->update(['read' => $read->merge([(string) $groupId, (string) $itemId])->unique()->implode(',')]);
        }
    }

    public function down()
    {
        $db = DB::connection('mysql_gomezapp');
        $ids = $db->table('menus')->where('url', '/admin/solicitudes-internas')->pluck('id');
        $groupId = $db->table('menus')->where('menu', 'Oficialía Mayor')->where('belongs_to', 0)->value('id');
        $remove = $ids->push($groupId)->filter()->map(fn($id) => (string) $id);
        foreach ($db->table('roles')->get() as $role) {
            if ($role->read === 'todas') continue;
            $read = collect(explode(',', (string) $role->read))->reject(fn($id) => $remove->contains((string) $id))->implode(',');
            $db->table('roles')->where('id', $role->id)->update(['read' => $read]);
        }
        $db->table('menus')->whereIn('id', $ids)->delete();
        if ($groupId) $db->table('menus')->where('id', $groupId)->delete();
    }
};
