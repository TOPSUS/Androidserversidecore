<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetodePembayaran extends Model
{
    use SoftDeletes;
    protected $table = 'tb_metode_pembayaran';
}
