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

        if($request->photo!=''){
            $photo = time().'.jpg';
            Storage::put('public_html/image_users',base64_decode($request->photo));
            //file_put_contents($photo,base64_decode($request->photo));
            // $simpan_image_profile = Storage::putFile('public_html/image_users',base64_decode($request->photo));
            // $simpan_image_profile = basename($simpan_image_profile);
            // //file_put_contents('public_html/image_users'.$photo,base64_decode($request->photo));
            //Storage::put('public_html/image_users/$photo',base64_decode($request->photo));
            // $user->foto = $simpan_image_profile;
            // File::put(public_path().'/project_images/'.$imageName, base64_decode($request->encoded_image));
        }

        // SIMPAN SEMUA PERUBAHAN
        $user->update();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'edit profile berhasil',
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
