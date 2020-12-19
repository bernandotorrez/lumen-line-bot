<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineUser extends Model
{
    protected $table = 'tbl_line_user';
    protected $primaryKey = 'id_line_user';
    protected $guarded = ['id_line_user'];

}
