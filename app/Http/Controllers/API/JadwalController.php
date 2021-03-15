<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jadwal;

class JadwalController extends Controller
{
    public function getJadwal(Request $request){
        $jadwals = Jadwal::whereDate('waktu_berangkat','2021-03-16')->get('id')->toArray();

        return  $jadwals;
        

        if($jadwals != null){
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil mendapatkan jadwal',
                'error' => (Object)[],
                'jadwal' => 
        ],200);

        
        }
        
    }
}
