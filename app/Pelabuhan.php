<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelabuhan extends Model
{
    use SoftDeletes;
    protected $table = 'tb_pelabuhan';

    public function getGolongan(){
        return $this->hasMany('App\Golongan','id_pelabuhan','id')->get();
    }
}
