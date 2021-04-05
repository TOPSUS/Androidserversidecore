<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\BeritaPelabuhan;

class BeritaPelabuhanController extends Controller
{
    public function getAllBeritaPelabuhan(){
        $beritas = BeritaPelabuhan::all();
        
        $response_beritas = [];

        foreach ($beritas as $index => $berita) 
        {
            $response_beritas[] = [
              'id' => $berita->id,
              'id_pelabuhan' => $berita->id_pelabuhan,
              'id_user' => $berita->id_user,
              'judul' => $berita->judul,
              'foto' => $berita->foto,
              'berita' => strip_tags(htmlspecialchars_decode($berita->berita)),
              'tanggal' => $berita->tanggal,
            ];
        }

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[],
            'berita_pelabuhan' => $response_beritas,
        ]);
        
    }    
}
