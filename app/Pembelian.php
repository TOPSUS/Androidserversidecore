<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'tb_pembelian';

    public function getJadwal(){
        return $this->hasOne('App\Jadwal','id','id_jadwal')->first();
    }

    public function getPelabuhanAsal(){
        return $this->hasOne('App\Pelabuhan','id','id_asal_pelabuhan')->first();
    }

    public function getPelabuhanTujuan(){
        return $this->hasOne('App\Pelabuhan','id','id_tujuan_pelabuhan')->first();
    }

    public function getBoat(){
        return $this->hasOne('App\SpeedBoat','id','id_speedboat')->first();
    }
}
