<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblModelType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_model_type')) {
            Schema::create('tbl_model_type', function (Blueprint $table) {
                $table->id('id_model_type');
                $table->bigInteger('id_model');
                $table->string('nama_model_type', 150);
                $table->double('price');
                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('tbl_model_type');
    }
}
