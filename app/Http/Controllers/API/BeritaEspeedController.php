<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\BeritaEspeed;

class BeritaEspeedController extends Controller
{
    public function getAllBeritaEpseed(){
        $beritas = BeritaEspeed::all();

        $response_beritas = [];

        foreach ($beritas as $index => $berita) 
        {
            $response_beritas[] = [
                'id' => $berita->id,
                'id_speedboat' => $berita->id_speedboat,
                'id_user' => $berita->id_user,
                'judul' => $berita->judul,
                'berita' => $berita->berita,
                'tanggal' => $berita->tanggal,
                'foto' => $berita->foto,
            ]; 
        }

        return response()->json([
            'response_code' => '200',
            'status' => 'success',
            'error' => (Object)[],
            'berita_pelabuhan' => $response_beritas 
        ]);
        
    }
}
