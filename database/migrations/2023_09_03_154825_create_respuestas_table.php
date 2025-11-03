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
        Schema::create('reportes_respuestas', function (Blueprint $table) {

            $table->integer('id_reporte')->nullable();
            $table->string('respuesta');
            $table->string('motivo_reactivacion')->nullable();
            $table->boolean('Active')->default(true);
            $table->date('fecha_respuesta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reportes_respuestas');
    }
};
