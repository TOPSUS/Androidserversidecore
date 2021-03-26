<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Pembelian;
use App\Jadwal;

class PemesananController extends Controller
{
    public function createPemesanan(Request $request){
        // VALIDATOR REQUEST
            $penumpang_decode = \json_decode($request->penumpang);

            $validator = Validator::make([
                'id_pemesan' => $request->id_pemesan,
                'id_jadwal' => $request->id_jadwal,
                'penumpang' => $penumpang_decode
            ],[
                'id_pemesan' => 'required',
                'id_jadwal' => 'required',
                'penumpang' => 'required|array'
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
            // CEK APAKAN JADWAL MASIH TERSEDIA UNTUK SEMUA PENUMPANG
                $jadwal = Jadwal::find($request->id_jadwal);
                
                if($jadwal == null){
                    return response()->json([
                        'response_code' => 401,
                        'status' => 'failure',
                        'message' => 'Tidak ditemukan id_jadwal yang dimaksud',
                        'error' => (Object)[],
                    ],200);
                }
                
                $speedboat = $jadwal->getBoat();
                $total_pembelian_saat_ini = $jadwal->getTotalPembelianSaatini();
                
                if(($speedboat->kapasitas - $total_pembelian_saat_ini) >= count($penumpang_decode)){
                    return response()->json([
                        'response_code' => 200,
                        'status' => 'success',
                        'message' => 'berhasil create pemesanan',
                        'error' => (Object)[],
                    ],200);    
                }else{
                    return response()->json([
                        'response_code' => 402,
                        'status' => 'failure',
                        'message' => 'gagal jumlah ticket tersisa kurang',
                        'error' => (Object)[],
                    ],200);
    
                }
        // AKHIR
    }
}
