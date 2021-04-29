<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaPelabuhan extends Model
{
    use SoftDeletes;
    protected $table = 'tb_berita_pelabuhan';
}
