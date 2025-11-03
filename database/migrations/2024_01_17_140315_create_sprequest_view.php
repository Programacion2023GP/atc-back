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
  
        VIEW `spRequests` AS
        select sp_requests.*, departments.department,departments.director,asuntos.asunto FROM sp_requests 
        inner join departments on departments.id = sp_requests.id_departamento_destino
        inner join asuntos on sp_requests.id_asunto = asuntos.id 
        where sp_requests.active = 1
         
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS spRequests');
    }
};
