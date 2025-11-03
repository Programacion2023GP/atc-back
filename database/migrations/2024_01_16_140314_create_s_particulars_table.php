<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sp_requests', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_solicitud')->nullable();
            $table->string('folio')->nullable();
            $table->string('nombre')->nullable();
            $table->string('app')->nullable();
            $table->string('apm')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('cargo')->nullable();
            $table->string('telefono')->nullable();
            $table->string('cp')->nullable();
            $table->string('calle')->nullable();
            $table->string('num_ext')->nullable();
            $table->string('num_int')->nullable();
            $table->string('colonia')->nullable();
            $table->string('localidad')->nullable();
            $table->string('municipio')->nullable()->default('Gómez Palacio');
            $table->string('estado')->nullable()->default('Durango');
            $table->string('tipo_localidad')->nullable(); //urabo o rural
            $table->integer('id_departamento_destino')->nullable();
            $table->integer('id_asunto')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->string('observaciones')->nullable();
            $table->boolean('visto')->default(false);
            $table->dateTime('visto_at')->nullable();
            $table->string('respuesta')->nullable();
            $table->dateTime('respuesta_at')->nullable();
            $table->boolean('completado')->default(false);
            $table->dateTime('completado_at')->nullable();
            $table->integer('id_user_create')->nullable();
            $table->string('img_attach_1')->nullable();
            $table->string('img_attach_2')->nullable();
            $table->string('img_attach_3')->nullable();
            $table->string('img_attach_4')->nullable();
            $table->string('img_attach_5')->nullable();
            $table->string('img_stationery_1')->nullable();
            $table->string('img_stationery_2')->nullable();
            $table->string('img_stationery_3')->nullable();
            $table->string('img_stationery_4')->nullable();
            $table->string('img_stationery_5')->nullable();
            $table->boolean('active')->default(true);
            $table->string('estatus')->default("ALTA");
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sp_requests');
    }
};
