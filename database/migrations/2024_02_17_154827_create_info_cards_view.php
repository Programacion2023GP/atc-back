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
        DB::statement("
        CREATE 
        VIEW `info_cards` AS
            SELECT 
                `asuntos`.`asunto` AS `asunto`,
                `asuntos`.`bg_card` AS `bg_card`,
                `asuntos`.`bg_circle` AS `bg_circle`,
                `ra`.`id_reporte` AS `id_reporte`,
                `reportes`.`active` AS `activo`,
                COUNT(0) AS `Total`
            FROM
                ((`reportes_asuntos` `ra`
                JOIN `asuntos` ON ((`asuntos`.`id` = `ra`.`id_asunto`)))
                JOIN `reportes` ON ((`reportes`.`id` = `ra`.`id_reporte`)))
            WHERE
                ((`reportes`.`active` = 1)
                    AND `reportes`.`id` IN (SELECT 
                        `reportes_respuestas`.`id_reporte`
                    FROM
                        `reportes_respuestas`)
                    IS FALSE)
            GROUP BY `ra`.`id_asunto`
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS info_cards');
    }
};
