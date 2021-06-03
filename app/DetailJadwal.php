<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailJadwal extends Model
{
    use SoftDeletes;

    protected $table = "tb_detail_jadwal";

    public function getPembelian(){
        return $this->hasMany('App\Pembelian','id_jadwal','id');
    }

    public function getJadwal(){
        return $this->hasOne('App\Jadwal','id','id_jadwal');
    }
}