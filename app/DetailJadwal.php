<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailJadwal extends Model
{
    protected $table = "tb_detail_jadwal";

    public function getPembelian(){
        return $this->hasMany('App\Pembelian','id','id_jadwal');
    }

    public function getJadwal(){
        return $this->hasOne('App\Jadwal','id','id_jadwal');
    }
}