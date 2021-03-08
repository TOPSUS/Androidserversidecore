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
            'password' => 'required|min:3|max:50'
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

            $token =  $user->createToken('nApp')->accessToken;

            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'register berhasil dilakukan',
                'error' => (Object)[],
                'token' => $token,
                'user_id' => $user->id,
                'name' => $user->nama,
                'alamat' => $user->alamat,
                'chat_id' => $user->chat_id,
                'pin' => $user->pin,
                'email' => $user->email,
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
            'jeniskelamin' => 'in:Laki-laki,Peremuan',
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
            Storage::delete('public/image_users/'.$user->image);
            $simpan_image_profile = Storage::putFile('public/image_users',$request->file('imageprofile'));
            $simpan_image_profile = basename($simpan_image_profile);
        }else{
            // HAPUS KALAU ADA GAMBAR
            Storage::delete('public/image_users/'.$user->image);
            $simpan_image_profile = Storage::putFile('public/image_users',$request->file('imageprofile'));
            $simpan_image_profile = basename($simpan_image_profile);
        }

        // SIMPAN NAMA FOTO KE TABLE USER
        $user->foto = $simpan_image_profile;


        // SIMPAN SEMUA PERUBAHAN
        $user->save();

        // BUAT EMAIL
        $data = [
            "nama" => $user->nama,
            "link" => "ling lung"
        ];

        // KIRIM
        \Mail::to("alingotama14@gmail.com")->send(new VerifikasiUser($data));
        \Mail::to("alindeveloper14@gmail.com")->send(new VerifikasiUser($data));

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

}
