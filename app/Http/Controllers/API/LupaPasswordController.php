<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;

class LupaPasswordController extends Controller
{
    /**
     * VERIFIKASI DULU EMAIL YANG MAU GANTI PASSWORD
     * DENGAN CARA MENGIRIMKAN CODE VERIFIKASI KE EMAIL
     * USER YANG AKAN GANTI EMAIL
     */
    public function verifikasiEmailLupaPassword(Request $request){
        // VALIDASI EMAIL TERLEBIH DAHULU
            $validator = Validator::make($request->all(),[
                'email' => 'email'
            ]);

            // RETURN ERROR KALAU INPUT SALAH
                if($validator->fails()){
                    return response()->json([
                        'response_code' => 403,
                        'status' => 'failure',
                        'message' => 'access forbiden',
                        'errors' => $validator->errors()
                    ],200);
                }
            // AKHIR
        // AKHIR
        
        // MENCARI USER DENGAN EMAIL YANG DIMAKSUD DAN MENGIRIMKAN VERIFIIKASI CODE KE EMAIL
            $user = User::where('email',$request->email)->first();
            
            if($user != null){
                function generateRandomString($length = 5) {
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < $length; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }
                    return $randomString;
                }

                // RETURN RESPONSE SUKSES
                    return response()->json([
                        'response_code' => 200,
                        'status' => 'success',
                        'message' => 'berhasil generate kode verifikasi silahkan cek email',
                        'errors' => (Object)[]
                    ],200);
                // AKHIR
            }
            else{
                // APABILA USER TIDAK DITEMUKAN DI DALAM DATABASE OLEH SISTEM
                    return response()->json([
                        'response_code' => 403,
                        'status' => 'failure',
                        'message' => 'user tidak ditemukan dengan email terkait',
                        'errors' => (Object)[]
                    ],200);
                // AKHIR
            }
        // AKHIR
    }
}
