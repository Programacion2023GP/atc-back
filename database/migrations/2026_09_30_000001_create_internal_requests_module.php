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
        if (!Schema::connection(self::CONNECTION)->hasColumn('departments', 'can_issue_internal_requests')) {
            Schema::connection(self::CONNECTION)->table('departments', function (Blueprint $table) {
                $table->boolean('can_issue_internal_requests')->default(false)->after('active')->index();
            });
        }

        if (!Schema::connection(self::CONNECTION)->hasTable('internal_request_folio_sequences')) Schema::connection(self::CONNECTION)->create('internal_request_folio_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 12);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['prefix', 'year']);
        });

        if (!Schema::connection(self::CONNECTION)->hasTable('internal_requests')) Schema::connection(self::CONNECTION)->create('internal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 40)->nullable()->unique();
            $table->string('folio_prefix', 12)->default('OM');
            $table->unsignedSmallInteger('folio_year');
            $table->unsignedInteger('folio_number')->nullable();
            $table->unsignedBigInteger('origin_department_id');
            $table->unsignedBigInteger('destination_department_id');
            $table->unsignedBigInteger('created_by');
            $table->string('subject', 255);
            $table->json('body_json');
            $table->text('body_text');
            $table->enum('tracking_mode', ['SOLO_SEGUIMIENTO', 'PLAZO_CONFIGURADO'])->default('SOLO_SEGUIMIENTO');
            $table->unsignedSmallInteger('business_days')->nullable();
            $table->enum('status', ['BORRADOR', 'ENVIADA', 'RECIBIDA', 'EN_PROCESO', 'RESPONDIDA', 'CERRADA'])->default('BORRADOR');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['destination_department_id', 'status']);
            $table->index(['origin_department_id', 'status']);
        });

        if (!Schema::connection(self::CONNECTION)->hasTable('internal_request_events')) Schema::connection(self::CONNECTION)->create('internal_request_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_request_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type', 50);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['internal_request_id', 'created_at']);
        });

        if (!Schema::connection(self::CONNECTION)->hasTable('internal_request_sla_cycles')) Schema::connection(self::CONNECTION)->create('internal_request_sla_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_request_id');
            $table->unsignedSmallInteger('cycle_number');
            $table->enum('tracking_mode', ['SOLO_SEGUIMIENTO', 'PLAZO_CONFIGURADO']);
            $table->unsignedSmallInteger('business_days')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
            $table->unique(['internal_request_id', 'cycle_number'], 'ir_sla_request_cycle_unique');
        });

        if (!Schema::connection(self::CONNECTION)->hasTable('internal_request_responses')) Schema::connection(self::CONNECTION)->create('internal_request_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_request_id');
            $table->unsignedBigInteger('user_id');
            $table->text('response');
            $table->timestamps();
            $table->index('internal_request_id');
        });

        if (!Schema::connection(self::CONNECTION)->hasTable('internal_request_files')) Schema::connection(self::CONNECTION)->create('internal_request_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_request_id');
            $table->unsignedBigInteger('user_id');
            $table->string('kind', 30)->default('EVIDENCIA');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size');
            $table->timestamps();
            $table->index('internal_request_id');
        });

        if (Schema::connection(self::CONNECTION)->hasTable('internal_request_sla_cycles')) {
            $index = DB::connection(self::CONNECTION)->select("SHOW INDEX FROM internal_request_sla_cycles WHERE Key_name = 'ir_sla_request_cycle_unique'");
            if (!$index) Schema::connection(self::CONNECTION)->table('internal_request_sla_cycles', function (Blueprint $table) {
                $table->unique(['internal_request_id', 'cycle_number'], 'ir_sla_request_cycle_unique');
            });
        }

        DB::connection(self::CONNECTION)->table('departments')
            ->whereRaw('UPPER(TRIM(department)) = ?', ['OFICIALIA MAYOR'])
            ->update(['can_issue_internal_requests' => true]);
    }

    public function down()
    {
        foreach (['internal_request_files', 'internal_request_responses', 'internal_request_sla_cycles', 'internal_request_events', 'internal_requests', 'internal_request_folio_sequences'] as $table) {
            Schema::connection(self::CONNECTION)->dropIfExists($table);
        }
        Schema::connection(self::CONNECTION)->table('departments', function (Blueprint $table) {
            $table->dropColumn('can_issue_internal_requests');
        });
    }
};
