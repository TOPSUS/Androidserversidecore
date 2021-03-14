<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Pelabuhan;
class PelabuhanController extends Controller
{
    public function readAllPelabuhan(){
        $pelabuhans = Pelabuhan::all();

        $response_pelabuhan = [];

        foreach ($pelabuhans as $index => $pelabuhan) 
        {
            $response_pelabuhan[] = [
                'id' => $pelabuhan->id,
                'kode_pelabuhan' => $pelabuhan->kode_pelabuhan,
                'lokasi_pelabuhan' => $pelabuhan->lokasi_pelabuhan,
                'alamat_kantor' => $pelabuhan->alamat_kantor,
                'latitude' => $pelabuhan->latitude,
                'longtitude' => $pelabuhan->longtitude,
                'lama_beroperasi' => $pelabuhan->lama_beroperasi,
                'status' => $pelabuhan->status
            ];
        }

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[],
            'berita_pelabuhan' => $response_pelabuhan,
        ]);
    }
}
