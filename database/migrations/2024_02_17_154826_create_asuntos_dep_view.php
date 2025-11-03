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
        VIEW `asuntos_dep` AS
        SELECT 
            `departamentos_asuntos`.`department_id` AS `department_id`,
            `departments`.`department` AS `department`,
            `departamentos_asuntos`.`asunto_id` AS `asunto_id`,
            `asuntos`.`asunto` AS `asunto`
        FROM
            (`departamentos_asuntos`
            JOIN `asuntos` ON ((`asuntos`.`id` = `departamentos_asuntos`.`asunto_id`)))
            JOIN `departments` ON ((`departments`.`id` = `departamentos_asuntos`.`department_id`))
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS asuntos_dep');
    }
};
