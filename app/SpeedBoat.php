<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeedBoat extends Model
{
    use softDeletes;
    protected $table ='tb_kapal';
}
