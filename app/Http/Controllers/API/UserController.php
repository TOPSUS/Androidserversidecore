<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\User;

class UserController extends Controller
{
    public function detail(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'authentikasi gagal dilakukan',
                'error' => [],
            ],200);
        }

        $user = User::find($request->id);

        if($user == null){
            return response()->json([
                'response_code' => 401,
                'status' => 'tidak ada user yang dimaksud',
                'message' => 'tidak ada user yang dimaksud',
                'error' => [],
            ],200);
        }

            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'register berhasil dilakukan',
                'error' => [],
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
}
