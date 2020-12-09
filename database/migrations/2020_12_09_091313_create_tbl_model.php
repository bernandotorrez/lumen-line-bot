<?php

use App\Models\CarModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_model')) {
            Schema::create('tbl_model', function (Blueprint $table) {
                $table->id('id_model');
                $table->string('nama_model', 100);
                $table->timestamps();
                $table->softDeletes();
            });
        }
        
        $this->insertData();
    }

    private function insertData()
    {
        CarModel::create([
            'nama_model' => '718'
        ]);

        CarModel::create([
            'nama_model' => '911'
        ]);

        CarModel::create([
            'nama_model' => 'Panamera'
        ]);

        CarModel::create([
            'nama_model' => 'Macan'
        ]);

        CarModel::create([
            'nama_model' => 'Cayenne'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_model');
    }
}
