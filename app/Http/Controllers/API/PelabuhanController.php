<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Pelabuhan;
class PelabuhanController extends Controller
{
    public function readAllPelabuhanSpeedBoat(){
        $pelabuhans = Pelabuhan::where('tipe_pelabuhan','Speedboat')->orWhere('tipe_pelabuhan','Speedboat & Kapal')->get();

        $response_pelabuhan = [];

        foreach ($pelabuhans as $index => $pelabuhan) 
        {
            $response_pelabuhan[] = [
                'id' => $pelabuhan->id,
                'kode_pelabuhan' => $pelabuhan->kode_pelabuhan,
                'nama_pelabuhan' => $pelabuhan->nama_pelabuhan,
                'lokasi_pelabuhan' => $pelabuhan->lokasi_pelabuhan,
                'alamat_kantor' => $pelabuhan->alamat_kantor,
                'foto' => $pelabuhan->foto,
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
            'pelabuhan' => $response_pelabuhan,
        ]);
    }
}
