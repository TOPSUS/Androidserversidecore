<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Jobs\EmailSender;

use App\User;

class LupaPasswordController extends Controller
{
    /**
     * VERIFIKASI DULU EMAIL YANG MAU GANTI PASSWORD
     * DENGAN CARA MENGIRIMKAN CODE VERIFIKASI KE EMAIL
     * USER YANG AKAN GANTI EMAIL
     */
    public function verifikasiEmailLupaPassword(Request $request)
    {
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

                $user->kode_verifikasi_email = $this->generateRandomString();
                $user->update();

                $data = [
                    'kode_verifikasi' => $user->kode_verifikasi_email,
                    'email' => $user->email,
                    'nama' => $user->nama
                ];
                
                EmailSender::dispatch($data)->afterResponse();

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

    /**
     * 
     * VERIFIKASI YANG DILAKUKAN DENGAN TELEGRAM
     * HANYA MENGGUNAKAN SATU METHOD DENGAN PARAMS EMAIL DAN PIN DARI USER
     * 
     */
    public function telegramLupaPassword(Request $request){
        // VALIDATOR
            $validator = Validator::make($request->all(),[
                'email' => 'required|email',
                'pin' => 'required|numeric',
            ]);
            
            if($validator->fails()){
                return response()->json([
                    'response_code' => 403,
                    'status' => 'failure',
                    'message' => 'access forbiden',
                    'errors' => $validator->errors(),
                ],200);
            };
        // AKHIR

        // MEMBENTUK PASSWORD BARU
            $user = User::where('email',$request->email)->where('pin',$request->pin)->first();
            
            if($user != null){
                $newPass = $this->generateRandomString();

                $pass = Hash::make($newPass);

                $user->password = $pass;

                $user->update();

                return response()->json([
                    'response_code' => 200,
                    'status' => 'success',
                    'message' => 'password diperbarui oleh sistem',
                    'errors' => $validator->errors(),
                    'new_pass' => $newPass
                ],200);
            }
            else{
                // SAAT TIDAK ADA USER YANG COCOK
                return response()->json([
                    'response_code' => 403,
                    'status' => 'failure',
                    'message' => 'tidak ada user yang cocok',
                    'errors' => $validator->errors(),
                ],200);
            }
        // AKHIR

    }

    // FUNGSI UNTUK GENERATE RANDOM STRING
    private function generateRandomString($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
