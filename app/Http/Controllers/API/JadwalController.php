<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jadwal;

class JadwalController extends Controller
{
    public function getJadwal(Request $request){
        $jadwals = Jadwal::wherDate('waktu_berangkat','2021-03-16')->get();
        return $jadwals;
        
    }
}
