<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventLog extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_event_log';
    protected $primaryKey = 'id_event_log';
    protected $guarded = ['id_event_log'];

}