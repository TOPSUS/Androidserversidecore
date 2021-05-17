<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'tb_pembelian';

    public function getJadwal(){
        return $this->hasOne('App\Jadwal','id','id_jadwal')->withTrashed()->first();
    }

    public function getPembayaran(){
        return $this->hasOne('App\MetodePembayaran','id','id_metode_pembayaran')->withTrashed()->first();
    }

    public function getGolongan(){
        return $this->hasOne('App\Golongan','id','id_golongan')->withTrashed()->first();
    }

    public function getReview(){
        return $this->hasOne('App\Review','id_pembelian','id')->withTrashed()->first();
    }
}
