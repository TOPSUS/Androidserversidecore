<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Pelabuhan;
class PelabuhanController extends Controller
{
    public function readAllPelabuhan(Request $request){
        // LARAVEL VALIDATION
            $validator = Validator::make($request->all(),[
                'tipe_kapal' => 'required|in:speedboat,feri'
            ]);

            if($validator->fails()){
                return response()->json([
                    'response_code' => 403,
                    'status' => 'failure',
                    'message' => 'gagal mendapatkan data pelabuhan',
                    'error' => $validator->errors(),
                ]);
            }
        // AKHIR
            $pelabuhans = Pelabuhan::where('tipe_pelabuhan',$request->tipe_kapal)->orWhere('tipe_pelabuhan','speedboat & feri')
                            ->get(
                                [
                                    'id',
                                    'kode_pelabuhan',
                                    'nama_pelabuhan',
                                    'lokasi_pelabuhan',
                                    'alamat_kantor',
                                    'foto',
                                    'latitude',
                                    'longtitude',
                                    'lama_beroperasi',
                                    'tipe_pelabuhan',
                                    'status'
                                ]
                            );

        // RESPONSE JADWAL DENGAN STATUS BERHASIL
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[],
            'pelabuhan' => $pelabuhans,
        ]);
    }
}
