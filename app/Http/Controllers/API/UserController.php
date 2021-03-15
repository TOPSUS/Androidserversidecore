<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\User;

class UserController extends Controller
{
    public function detail()
    {
        
        $user = User::find(Auth::user()->id);

        if($user == null){
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'tidak ada user yang dimaksud',
                'error' => (Object)[],
            ],200);
        }

            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => ' berhasil dilakukan',
                'error' => (Object)[],
                'token' => '',
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

    public function editProfile(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $user->nama = $request->nama;
        $user->alamat = $request->alamat;
        $user->jeniskelamin = $request->jeniskelamin;
        $user->nohp = $request->nohp;
        $user->email = $request->email;

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
        $user->update();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'register berhasil dilakukan',
            'error' => (Object)[],
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
}
