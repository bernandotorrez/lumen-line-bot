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
                $table->text('img_url');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        
        $this->insertData();
    }

    private function insertData()
    {
        CarModel::create([
            'nama_model' => '718',
            'img_url' => 'https://files.porsche.com/filestore/image/multimedia/none/982-718-c7s-modelimage-sideshot/thumbwhite/0902e663-b529-11ea-80ca-005056bbdc38;sK/porsche-thumbwhite.jpg'
        ]);

        CarModel::create([
            'nama_model' => '911',
            'img_url' => 'https://files.porsche.com/filestore/image/multimedia/none/992-c2cab-modelimage-sideshot/thumbwhite/f41d6013-fa50-11e9-80c6-005056bbdc38;sK/porsche-thumbwhite.jpg'
        ]);

        CarModel::create([
            'nama_model' => 'Panamera',
            'img_url' => 'https://files.porsche.com/filestore/image/multimedia/none/971-g2-2nd-4s-modelimage-sideshot/thumbwhite/67ecf0a7-fd8f-11ea-80ce-005056bbdc38;sK/porsche-thumbwhite.jpg'
        ]);

        CarModel::create([
            'nama_model' => 'Macan',
            'img_url' => 'https://files.porsche.com/filestore/image/multimedia/none/pa-gts-modelimage-sideshot/thumbwhite/7dc6eb2c-11fe-11ea-80c6-005056bbdc38;sK/porsche-thumbwhite.jpg'
        ]);

        CarModel::create([
            'nama_model' => 'Cayenne',
            'img_url' => 'https://files.porsche.com/filestore/image/multimedia/none/9yb-e3-c-modelimage-sideshot/thumbwhite/c80c6076-fa4d-11e9-80c6-005056bbdc38;sK/porsche-thumbwhite.jpg' 
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
