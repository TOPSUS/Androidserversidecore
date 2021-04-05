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
        return $this->hasOne('App\SpeedBoat','id','id_kapal')->where('tipe_kapal','speedboat')->first();
    }

    public function getKapal(){
        return $this->hasOne('App\SpeedBoat','id','id_kapal')->where('tipe_kapal','feri')->first();
    }

    public function getTotalPembelianSaatini(){
        return $this->hasMany('App\Pembelian','id_jadwal','id')->where('status','Terkonfirmasi')->count();
    }
}
