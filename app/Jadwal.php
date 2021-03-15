<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'tb_jadwal';

    public function getPelabuhanAsal(){
        return $this->hasOne('App\Pelabuhan','id','id_asal_pelabuhan')->first();
    }

    public function getPelabuhanTujuan(){
        return $this->hasOne('App\Pelabuhan','id','id_tujuan_pelabuhan')->first();
    }

    public function getBoat(){
        return $this->hasOne('App\SpeedBoat','id','id_speedboat')->first();
    }

    public function getPembelian(){
        return $this->hasMany('App\Pembelian','id_jadwal','id')->get();
    }
}
