<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $table = 'tb_refund';

    public function getPembelian(){
        return $this->hasOne('App\Pembelian','id','id_pembelian')->withTrashed()->first();
    }
}
