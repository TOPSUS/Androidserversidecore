<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\User;

class AuthController extends Controller
{
    /**
     * ROUTE YANG DIGUNAKAN UNTUK LOGIN USER
     * SETELAH LOGIN MAKA USER AKAN DIBERIKAN AUTH TOKEN
     */
    public function login(Request $request){
        if(Auth::attempt(['email' => $request->email, 'password' => $request->username])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('nApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 200);
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
            'foto' => 'nullable|image|max:1000',
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
        $user->nama = $request->name;
        $user->alamat = $request->alamat;
        $user->jeniskelamin = $request->jeniskelamin;
        $user->nohp = $request->nohp;
        $user->email = $request->email;
        $user->foto = "test";
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'register berhasil dilakukan',
            'error' => [],
            'user_id' => $user->id,
            'name' => $request->nama,
            'email' => $request->email,
            'nohp' => $request->nohp,
            'jeniskelamin' => $request->jeniskelamin
        ],200);
    }

}
