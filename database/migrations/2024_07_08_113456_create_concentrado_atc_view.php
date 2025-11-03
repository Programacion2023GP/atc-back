<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement("
        create view vw_concentrado_atc as
SELECT 
department,
 IFNULL(asunto, CONCAT('TOTAL-', department)) AS asunto,
    count(asunto) as PETICIONES,
	SUM(CASE WHEN estatus = 'TERMINADO' THEN 1 ELSE 0 END) AS TERMINADO,
	SUM(CASE WHEN estatus = 'NO PROCEDE' THEN 1 ELSE 0 END) AS NO_PROCEDE,
    SUM(CASE WHEN estatus = 'EN TRAMITE' THEN 1 ELSE 0 END) AS EN_TRAMITE,
     SUM(
        CASE WHEN estatus = 'EN TRAMITE' THEN 1 ELSE 0 END +
        CASE WHEN estatus = 'TERMINADO' THEN 1 ELSE 0 END +
	    CASE WHEN estatus = 'NO PROCEDE' THEN 1 ELSE 0 END
    ) AS TOTAL,
	COUNT(asunto) - SUM( CASE WHEN estatus = 'EN TRAMITE' THEN 1 ELSE 0 END +
        CASE WHEN estatus = 'TERMINADO' THEN 1 ELSE 0 END +
	    CASE WHEN estatus = 'NO PROCEDE' THEN 1 ELSE 0 END) AS PENDIENTES,
        fecha_reporte
FROM 
    reports
GROUP BY 
    department,
    fecha_reporte,
    asunto
    order by department, asunto

         ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vw_concentrado_atc');
    }
};
