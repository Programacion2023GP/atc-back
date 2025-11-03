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
        Schema::table('reportes', function (Blueprint $table) {
            $table->string('img_job')->nullable()->after('community_id');
            $table->string('img_zone')->nullable()->after('img_job');
            $table->string('img_evidence_worker_1')->nullable()->after('img_zone');
            $table->string('img_evidence_worker_2')->nullable()->after('img_evidence_worker_1');
            $table->string('img_evidence_worker_3')->nullable()->after('img_evidence_worker_2');
            $table->string('img_evidence_worker_4')->nullable()->after('img_evidence_worker_3');
            $table->string('img_evidence_worker_5')->nullable()->after('img_evidence_worker_4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn('img_job');
            $table->dropColumn('img_zone');
            $table->dropColumn('img_evidence_worker_1');
            $table->dropColumn('img_evidence_worker_2');
            $table->dropColumn('img_evidence_worker_3');
            $table->dropColumn('img_evidence_worker_4');
            $table->dropColumn('img_evidence_worker_5');
        });
    }
};
