<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pelabuhan extends Model
{
    protected $table = 'tb_pelabuhan';

    public function getGolongan(){
        return $this->hasMany('App\Golongan','id_pelabuhan','id')->get();
    }
}
