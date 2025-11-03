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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_reporte')->nullable();
            $table->string('img_reporte')->nullable();
            $table->string('folio')->nullable();
            $table->string('latitud')->nullable(); //->unique();
            $table->string('longitud')->nullable();
            $table->integer('id_user')->nullable();
            $table->string('cp')->nullable();
            $table->string('calle')->nullable();
            $table->string('num_ext')->nullable();
            $table->string('num_int')->nullable();
            $table->string('colonia')->nullable();
            $table->string('localidad')->nullable()->default('Gómez Palacio');
            $table->string('municipio')->nullable()->default('Gómez Palacio');
            $table->string('estado')->nullable()->default('Durango');
            $table->string('referencias')->nullable();
            $table->integer('id_departamento')->nullable();
            $table->integer('id_origen')->nullable();; //WEB, APP, TELEFONICO ETC
            $table->integer('id_estatus');   // ASIGANDO, EN CURSO, ATENDIDO ETC
            $table->string('community_id')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('reportes');
    }
};
