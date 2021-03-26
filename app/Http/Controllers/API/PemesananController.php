<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Pembelian;

class PemesananController extends Controller
{
    public function createPemesanan(Request $request){
        // VALIDATOR REQUEST
            $penumpang_decode = \json_decode($request->penumpang);

            $validator = Validator::make([
                'id_pemesan' => $request->id_pemesanan,
                'id_jadwal' => $request->id_jadwal,
                'penumpang' => $penumpang_decode
            ],[
                'id_pemesan' => 'required',
                'id_jadwal' => 'required',
                'penumpang' => 'array'
            ]);

            if($validator->fails()){
                return response()->json([
                    'response_code' => 402,
                    'status' => 'failure',
                    'message' => 'terdapat format penulisan parameter yang salah',
                    'error' => $validator->errors(),
                ],200);
            }
        // AKHIR

        // MAIN LOGIC BUAT SEBUAH PEMESANAN
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil create pemesanan',
                'error' => (Object)[],
            ],200);
        // AKHIR
    }
}
