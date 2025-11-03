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
        VIEW `userdep` AS
            select ud.id,ud.user_id, u.email,ud.departamento_id,d.department  from usuarios_departamentos as ud
inner join users as u on ud.user_id = u.id
inner join departments as d on ud.departamento_id = d.id
order by u.email
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userdep');
    }
};
