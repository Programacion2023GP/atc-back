<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'mysql_gomezapp';

    public function up()
    {
        $schema = Schema::connection(self::CONNECTION);
        $columns = [
            'incoming_office_number' => fn (Blueprint $table) => $table->string('incoming_office_number', 80)->nullable(),
            'incoming_office_at' => fn (Blueprint $table) => $table->dateTime('incoming_office_at')->nullable(),
            'incoming_origin_department_id' => fn (Blueprint $table) => $table->unsignedBigInteger('incoming_origin_department_id')->nullable()->index(),
            'incoming_destination_department_id' => fn (Blueprint $table) => $table->unsignedBigInteger('incoming_destination_department_id')->nullable()->index(),
            'incoming_received_by_user_id' => fn (Blueprint $table) => $table->unsignedBigInteger('incoming_received_by_user_id')->nullable()->index(),
            'received_by' => fn (Blueprint $table) => $table->unsignedBigInteger('received_by')->nullable()->index(),
            'started_by' => fn (Blueprint $table) => $table->unsignedBigInteger('started_by')->nullable()->index(),
            'attention_plan' => fn (Blueprint $table) => $table->text('attention_plan')->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            if (! $schema->hasColumn('internal_requests', $name)) {
                $schema->table('internal_requests', fn (Blueprint $table) => $definition($table));
            }
        }

        $db = DB::connection(self::CONNECTION);
        $menuId = $db->table('menus')->where('url', '/admin/solicitudes-internas')->where('type', 'item')->value('id');
        if ($menuId) {
            $permissions = [
                'Redactar Solicitud Interna',
                'Aprobar Solicitud Interna',
                'Confirmar Recepción',
                'Iniciar Atención',
                'Responder',
            ];
            $db->table('menus')->where('id', $menuId)->update([
                'others_permissions' => implode(',', $permissions),
                'updated_at' => now(),
            ]);

            // Conserva las capacidades que ya tenían los roles participantes.
            $participantRoleIds = $db->table('users')
                ->join('usuarios_departamentos as ud', 'ud.user_id', '=', 'users.id')
                ->distinct()->pluck('users.role_id');
            $issuerRoleIds = $db->table('users')
                ->join('usuarios_departamentos as ud', 'ud.user_id', '=', 'users.id')
                ->join('departments as d', 'd.id', '=', 'ud.departamento_id')
                ->where('d.can_issue_internal_requests', true)
                ->distinct()->pluck('users.role_id');

            foreach ($db->table('roles')->whereIn('id', $participantRoleIds)->get() as $role) {
                if ($role->more_permissions === 'todas') continue;
                $current = collect(explode(',', (string) $role->more_permissions))->map(fn ($value) => trim($value))->filter();
                $granted = ['Confirmar Recepción', 'Iniciar Atención', 'Responder'];
                if ($issuerRoleIds->contains($role->id)) {
                    $granted[] = 'Redactar Solicitud Interna';
                    $granted[] = 'Aprobar Solicitud Interna';
                }
                $db->table('roles')->where('id', $role->id)->update(['more_permissions' => $current->merge($granted)->unique()->implode(',')]);
            }
        }
    }

    public function down()
    {
        $schema = Schema::connection(self::CONNECTION);
        $columns = [
            'incoming_office_number', 'incoming_office_at', 'incoming_origin_department_id',
            'incoming_destination_department_id', 'incoming_received_by_user_id',
            'received_by', 'started_by', 'attention_plan',
        ];
        $existing = array_values(array_filter($columns, fn ($column) => $schema->hasColumn('internal_requests', $column)));
        if ($existing !== []) $schema->table('internal_requests', fn (Blueprint $table) => $table->dropColumn($existing));
    }
};
