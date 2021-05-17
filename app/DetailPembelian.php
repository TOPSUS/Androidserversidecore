<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    protected $table = 'tb_detail_pembelian';

    public function getCard(){
        return $this->hasOne('App\Card','id','id_card')->withTrashed()->first();
    }
}
