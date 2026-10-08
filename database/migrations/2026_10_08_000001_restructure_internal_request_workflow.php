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
        $requestColumns = [
            'decision' => fn (Blueprint $table) => $table->string('decision', 100)->nullable()->index(),
            'decision_by' => fn (Blueprint $table) => $table->unsignedBigInteger('decision_by')->nullable()->index(),
            'decided_at' => fn (Blueprint $table) => $table->dateTime('decided_at')->nullable(),
            'redirected_by' => fn (Blueprint $table) => $table->unsignedBigInteger('redirected_by')->nullable()->index(),
            'reopen_reason' => fn (Blueprint $table) => $table->text('reopen_reason')->nullable(),
        ];
        foreach ($requestColumns as $name => $definition) {
            if (! $schema->hasColumn('internal_requests', $name)) {
                $schema->table('internal_requests', fn (Blueprint $table) => $definition($table));
            }
        }

        if (! $schema->hasColumn('internal_request_responses', 'response_json')) $schema->table('internal_request_responses', fn (Blueprint $table) => $table->json('response_json')->nullable());
        if (! $schema->hasColumn('internal_request_responses', 'response_text')) $schema->table('internal_request_responses', fn (Blueprint $table) => $table->text('response_text')->nullable());

        DB::connection(self::CONNECTION)->statement("ALTER TABLE internal_requests MODIFY status ENUM('BORRADOR','ENVIADA','RECIBIDA','EN_PROCESO','RESPONDIDA','CERRADA','NO_AUTORIZADA') NOT NULL DEFAULT 'BORRADOR'");

        $db = DB::connection(self::CONNECTION);
        $menuId = $db->table('menus')->where('url', '/admin/solicitudes-internas')->where('type', 'item')->value('id');
        if (! $menuId) return;

        $specialPermissions = ['Redirigir Departamento', 'Resolver Solicitud Interna', 'Reabrir Solicitud Interna', 'Confirmar Recepción', 'Iniciar Atención', 'Responder'];
        $db->table('menus')->where('id', $menuId)->update(['others_permissions' => implode(',', $specialPermissions), 'updated_at' => now()]);

        $reportMenuId = $db->table('menus')->where('url', '/admin/reportes/solicitudes-internas')->value('id');
        if (! $reportMenuId) {
            $parentId = $db->table('menus')->where('menu', 'Oficialía Mayor')->where('belongs_to', 0)->value('id');
            $reportMenuId = $db->table('menus')->insertGetId([
                'menu' => 'Reportes de solicitudes internas', 'caption' => 'Indicadores, tiempos y cumplimiento',
                'type' => 'item', 'belongs_to' => $parentId, 'url' => '/admin/reportes/solicitudes-internas',
                'icon' => 'IconChartHistogram', 'order' => 2, 'active' => 1, 'show_counter' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        foreach ($db->table('roles')->get() as $role) {
            if ($role->more_permissions === 'todas') continue;
            $permissions = collect(explode(',', (string) $role->more_permissions))
                ->map(fn ($value) => trim($value))->filter()
                ->reject(fn ($value) => in_array($value, ['Redactar Solicitud Interna', 'Aprobar Solicitud Interna'], true));
            $db->table('roles')->where('id', $role->id)->update(['more_permissions' => $permissions->unique()->implode(',')]);
        }

        $appendMenuPermission = function (?string $current, int $id): string {
            if ($current === 'todas') return 'todas';
            return collect(explode(',', (string) $current))->map(fn ($value) => trim($value))->filter()->push((string) $id)->unique()->implode(',');
        };
        $appendSpecial = function (?string $current, array $permissions): string {
            if ($current === 'todas') return 'todas';
            return collect(explode(',', (string) $current))->map(fn ($value) => trim($value))->filter()->merge($permissions)->unique()->implode(',');
        };

        $assistant = $db->table('roles')->where('role', 'Asistente Oficialía Mayor')->first();
        $assistantData = [
            'description' => 'Recibe oficios en Oficialía Mayor y captura solicitudes internas en borrador.',
            'read' => $appendMenuPermission($assistant?->read, $menuId),
            'create' => $appendMenuPermission($assistant?->create, $menuId),
            'update' => $assistant?->update,
            'delete' => $appendMenuPermission($assistant?->delete, $menuId),
            'active' => 1, 'updated_at' => now(),
        ];
        if ($assistant) $db->table('roles')->where('id', $assistant->id)->update($assistantData);
        else $db->table('roles')->insert(array_merge($assistantData, ['role' => 'Asistente Oficialía Mayor', 'more_permissions' => '', 'created_at' => now()]));

        $official = $db->table('roles')->where('role', 'Oficial Mayor')->first();
        $officialData = [
            'description' => 'Revisa, corrige, redirige, resuelve y supervisa solicitudes internas de Oficialía Mayor.',
            'read' => $appendMenuPermission($appendMenuPermission($official?->read, $menuId), $reportMenuId),
            'create' => $appendMenuPermission($official?->create, $menuId),
            'update' => $appendMenuPermission($official?->update, $menuId),
            'delete' => $appendMenuPermission($official?->delete, $menuId),
            'more_permissions' => $appendSpecial($official?->more_permissions, ['Redirigir Departamento', 'Resolver Solicitud Interna', 'Reabrir Solicitud Interna']),
            'active' => 1, 'updated_at' => now(),
        ];
        if ($official) $db->table('roles')->where('id', $official->id)->update($officialData);
        else $db->table('roles')->insert(array_merge($officialData, ['role' => 'Oficial Mayor', 'created_at' => now()]));
    }

    public function down()
    {
        // Los campos se conservan para no destruir expedientes si se revierte código.
    }
};
