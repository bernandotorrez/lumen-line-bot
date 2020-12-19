<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblEventLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_event_log')) {
            Schema::create('tbl_event_log', function (Blueprint $table) {
                $table->id('id_event_log');
                $table->string('signature', 100);
                $table->text('event');
                $table->timestamp('timestamp')->useCurrent();
                $table->timestamps();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_event_log');
    }
}
