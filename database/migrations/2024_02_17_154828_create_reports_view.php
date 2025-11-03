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
        CREATE 
  
        VIEW `reports` AS
           SELECT 
        `reportes`.`id` AS `id`,
        `reportes`.`fecha_reporte` AS `fecha_reporte`,
        `reportes`.`cp` AS `cp`,
        `reportes`.`calle` AS `calle`,
        `reportes`.`num_ext` AS `num_ext`,
        `reportes`.`num_int` AS `num_int`,
        `reportes`.`colonia` AS `colonia`,
        `reportes`.`localidad` AS `localidad`,
        `reportes`.`estado` AS `estado`,
        `reportes`.`id_user` AS `id_user`,
        `reportes`.`community_id` AS `community_id`,
        `reportes`.`latitud` AS `latitud`,
        `reportes`.`longitud` AS `longitud`,
        `reportes`.`referencias` AS `referencias`,
        `reportes`.`img_reporte` AS `img_reporte`,
        `users`.`email` AS `email`,
        `users`.`role_id` AS `role_id`,
        `users`.`phone` AS `phone`,
        `users`.`name` AS `name`,
        `users`.`paternal_last_name` AS `paternal_last_name`,
        `users`.`maternal_last_name` AS `maternal_last_name`,
        `users`.`curp` AS `curp`,
        `users`.`sexo` AS `sexo`,
        `reportes`.`id_departamento` AS `id_departamento`,
        `departments`.`department` AS `department`,
        `reportes`.`id_origen` AS `id_origen`,
        `origen_reporte`.`origen` AS `origen`,
        `reportes`.`id_estatus` AS `id_estatus`,
        `estatus`.`estatus` AS `estatus`,
        `reportes_asuntos`.`id_servicio` AS `id_servicio`,
        `servicios`.`servicio` AS `servicio`,
        `reportes_asuntos`.`id_asunto` AS `id_asunto`,
        `asuntos`.`asunto` AS `asunto`,
        `reportes_asuntos`.`observaciones` AS `observaciones`,
        `reportes_respuestas`.`respuesta` AS `respuesta`,
		`reportes`.`img_job` AS `img_job`,
		`reportes`.`img_zone` AS `img_zone`,
        `reportes`.`img_evidence_worker_1` AS `img_evidence_worker_1`,
        `reportes`.`img_evidence_worker_2` AS `img_evidence_worker_2`,
        `reportes`.`img_evidence_worker_3` AS `img_evidence_worker_3`,
	    `reportes`.`img_evidence_worker_4` AS `img_evidence_worker_4`,
        `reportes`.`img_evidence_worker_5` AS `img_evidence_worker_5`
    FROM   ((((((((`reportes`
        JOIN `users` ON ((`users`.`id` = `reportes`.`id_user`)))
        JOIN `departments` ON ((`departments`.`id` = `reportes`.`id_departamento`)))
        JOIN `origen_reporte` ON ((`origen_reporte`.`id` = `reportes`.`id_origen`)))
        JOIN `estatus` ON ((`estatus`.`id` = `reportes`.`id_estatus`)))
        JOIN `reportes_asuntos` ON ((`reportes_asuntos`.`id_reporte` = `reportes`.`id`)))
        JOIN `servicios` ON ((`servicios`.`id` = `reportes_asuntos`.`id_servicio`)))
        JOIN `asuntos` ON ((`asuntos`.`id` = `reportes_asuntos`.`id_asunto`)))
        LEFT JOIN `reportes_respuestas` ON ((`reportes_respuestas`.`id_reporte` = `reportes`.`id`)))
    WHERE
        (`reportes`.`active` = 1)
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS reports');
    }
};
