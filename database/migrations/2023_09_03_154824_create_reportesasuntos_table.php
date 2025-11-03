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
        Schema::create('reportes_asuntos', function (Blueprint $table) {
        
            $table->integer('id_reporte')->nullable();
            $table->integer('id_servicio')->nullable(); //  QUEJA, SOSPECHA, DEMANDA ET
            $table->integer('id_asunto')->nullable();
            $table->string('observaciones');
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reportes_asuntos');
    }
};
