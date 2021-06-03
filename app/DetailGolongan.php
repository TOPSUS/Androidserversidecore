<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailGolongan extends Model
{
    protected $table = 'tb_detail_golongan';
    public $timestamps = false;

    public function getGolongan(){
        return $this->hasOne('App\Golongan','id','id_golongan');
    }
}
