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
        VIEW `request_departaments_status` AS
        select S.id , S.folio,D.department, S.estatus 
        from sp_requests as S
        left join departments as D 
        on S.id_departamento_destino = D.id 
        where S.active = 1 
        order by S.id_departamento_destino
           
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS request_departaments_status');
    }
};
