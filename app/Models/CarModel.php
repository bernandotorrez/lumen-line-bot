<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarModel extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_model';
    protected $primaryKey = 'id_model';
    protected $guarded = ['id_model'];

    // public function types()
    // {
    //     return $this->hasMany(CarModelType::class, 'id_model');
    // }
}