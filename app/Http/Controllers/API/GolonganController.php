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
}
