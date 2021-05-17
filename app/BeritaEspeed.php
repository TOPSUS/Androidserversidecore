<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaEspeed extends Model
{
    use SoftDeletes;
    protected $table = 'tb_berita_kapal';
}