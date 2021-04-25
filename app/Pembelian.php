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

    public function getPembayaran(){
        return $this->hasOne('App\MetodePembayaran','id','id_metode_pembayaran')->first();
    }

    public function getGolongan(){
        return $this->hasOne('App\Golongan','id','id_golongan')->first();
    }

    public function getReview(){
        return $this->hasOne('App\Review','id_pembelian','id')->first();
    }
}
