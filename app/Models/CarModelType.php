<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarModelType extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_car_model_type';
    protected $primaryKey = 'id_model_type';
    protected $guarded = ['id_model_type'];

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'id_model');
    }
}