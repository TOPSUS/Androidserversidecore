<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Golongan;

class GolonganController extends Controller
{
    public function getGolonganByPelabuhanId(Request $request){

        // LARAVEL VALIDATOR
        $validator = Validator::make($request->all(),[
            'id_pelabuhan' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'format ada yang salah',
                'error' => $validator->errors(),
                'golongans' => []
            ],200);
        }
        // AKHIR

        // MAIN LOGIC
        $golongans = Golongan::where('id_pelabuhan',$request->id_pelabuhan)->get(['id','id_pelabuhan','golongan','keterangan','harga',
                                    'created_at']);

        // RETURN BERHASIL
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'berhasil mendapatkan golongan',
            'error' => (Object)[],
            'golongans' => $golongans
        ],200);
        
    }

    /**
     * METHODE YANG DIGUNAKAN UNTUK, MENENTUKAN JUMLAH PENUMPANG YANG DAPAT DITAMPUNG SEBUAH
     * KAPAL.
     */
    public function getMaxJumlahPenumpang(Request $request){

        // VALIDATOR
        $validator = Validator::make($request->all(),[
            'id_golongan' => 'required|exists:tb_golongan,id',
        ],[
            'id_golongan.required' => 'id golongan tidak tersedia',
            'id_golongan.exists' => "tidak ada golongan"
        ]);
        
        if($validator->fails()){
            return response()->json([
                'response_code' => 400,
                'status' => 'failure',
                'message' => 'format tidak sesuai',
                'error' => $validator->errors(),
            ],200);
        }

        // MAIN LOGIC
        $max_jumlah_penumpang = Golongan::find($request->id_golongan)->max_penumpang;

        if($max_jumlah_penumpang == null){
            return response()->json([
                'response_code' => 400,
                'status' => 'failure',
                'message' => 'gagal terjadi kesalahan',
                'error' => $validator->errors(),
            ],200);
        }else{
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'maximal pemesanan didapatkan',
                'error' => (Object)[],
                'maximal_penumpang' => $max_jumlah_penumpang
            ],200);
        }
    }
}
