<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


use App\User;
use App\Mail\VerifikasiUser;


class AuthController extends Controller
{
    /**
     * ROUTE YANG DIGUNAKAN UNTUK LOGIN USER
     * SETELAH LOGIN MAKA USER AKAN DIBERIKAN AUTH TOKEN
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|min:3|max:50',
            'password' => 'required|min:3|max:50',
            'fcm_token' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'response_code' => 401,
                'status' => 'error_input',
                'message' => 'terdapat format penulisan yang salah',
                'error' => $validator->errors(),
            ],200);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();

            // UPDATE FCM_TOKEN
                // CARI DULU YANG MENGGUNAKAN TOKEN SEBELUMNYA
                    $user_with_same_fcm = User::where('fcm_token',$request->fcm_token)->get();
                    foreach ($user_with_same_fcm as $index => $same_fcm) {
                        $same_fcm->fcm_token = null;
                        $same_fcm->save();
                    }
                
                // UPDATE USER TERBARU
                    $user->fcm_token = $request->fcm_token;
                    $user->save();

            // CLEAR TOKEN PASSPORT USER SEBELUMNYA
            $user->tokens->each(function($token,$key){
                    $token->delete();
            });

            // MASUKKAN TOKEN PASSPORT BARU
            $token =  $user->createToken('nApp')->accessToken;

            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'login berhasil dilakukan',
                'error' => (Object)[],
                'token' => $token,
                'user_id' => $user->id,
                'name' => $user->nama,
                'alamat' => $user->alamat,
                'chat_id' => $user->chat_id,
                'pin' => $user->pin,
                'email' => $user->email,
                'foto' => $user->foto,
                'nohp' => $user->nohp,
                'jeniskelamin' => $user->jeniskelamin
            ],200);

        }
        else{
            return response()->json([
                'response_code' => 401,
                'status' => 'failed_login',
                'message' => 'Username atau password salah',
                'error' => (Object)[],
            ],200);
        }
    }

    /**
     * REGISTER YA BUAT REGISTER LAH COK
     */
    public function register(Request $request)
    {
        /**
         * 
         * VALIDATOR REGISTER, SEMUA INPUT DARI USER AKAN DISARING
         * DI SISI SERVER INI SELAIN DI SISI MOBILE JUGA
         * 
         */
        $validator = Validator::make($request->all(),[
            'nama' => 'required',
            'alamat' => 'required|min:3|max:200',
            'jeniskelamin' => 'in:Laki-laki,Perempuan',
            'nohp' => 'required|min:8|max:15',
            'email' => 'required|unique:tb_user,email',
            'imageprofile' => 'nullable|image|max:1000',
            'password' => 'required',
            'c_password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'terdapat format penulisan yang salah',
                'error' => $validator->errors(),
            ],200);
        }

    
        $user = new User;
        $user->nama = $request->nama;
        $user->alamat = $request->alamat;
        $user->jeniskelamin = $request->jeniskelamin;
        $user->nohp = $request->nohp;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        // PROSES PENYIMPANAN IMAGE BILA ADA
        if(!is_null($request->file('imageprofile'))){
            // HAPUS GAMBAR YANG SUDAH ADA
            Storage::delete('public_html/image_users/'.$user->image);
            $simpan_image_profile = Storage::putFile('public_html/image_users',$request->file('imageprofile'));
            $simpan_image_profile = basename($simpan_image_profile);
        }else{
            // HAPUS KALAU ADA GAMBAR
            Storage::delete('public_html/image_users/'.$user->image);
            $simpan_image_profile = 'default.png';
        }

        function generateRandomString($length = 20) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        // SIMPAN NAMA FOTO KE TABLE USER
        $user->foto = $simpan_image_profile;
        $user->token_login = generateRandomString();


        // SIMPAN SEMUA PERUBAHAN
        $user->save();

        // BUAT EMAIL
        $data = [
            "nama" => $user->nama,
            "link" => url('',['verify',$user->token_login]),
        ];

        // KIRIM
        \Mail::to($user->email)->send(new VerifikasiUser($data));

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'register berhasil dilakukan',
            'error' => (Object)[],
            'user_id' => $user->id,
            'name' => $request->nama,
            'email' => $request->email,
            'nohp' => $request->nohp,
            'jeniskelamin' => $request->jeniskelamin
        ],200);
    }

    public function failureMethod(){
        return response()->json([
            'response_code' => 401,
            'status' => 'failure',
            'message' => 'authentikasi gagal dilakukan',
            'error' => (Object)[],
        ],200);
    }

    public function verify($key){
        $user = User::where('token_login',$key)->first();

        if($user != null){
            $user->verified_at = date("Y-m-d H:i:s");
            $user->save();
            return view('verificationsuccess');
        }else{
            return "FAIL";
        }
    }

}
