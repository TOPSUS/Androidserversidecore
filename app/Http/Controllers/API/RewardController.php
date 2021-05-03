<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\User;
use App\Reward;
use App\SpeedBoat;
use App\Jadwal;

class RewardController extends Controller
{
    public function getPoin(Request $request)
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
        

        //$pembelians = Pembelian::where('id_user', $user->id)->where('id_golongan', NULL)->where('status', 'digunakan')->selectRaw("id_jadwal, SUM(poin) as total_poin")->getJadwal()->groupBy('id_kapal')->get();
        $pembelians = DB::table('tb_pembelian')
            ->join('tb_jadwal', 'tb_pembelian.id_jadwal', '=', 'tb_jadwal.id')
            ->join('tb_kapal', 'tb_jadwal.id_kapal', '=', 'tb_kapal.id')
            ->select('id_kapal', 'nama_kapal', DB::raw('SUM(tb_pembelian.poin) as total_poin'))
            ->where('id_user', $user->id)
            ->where('id_golongan', NULL)
            ->where('status', 'digunakan')
            ->groupBy('tb_jadwal.id_kapal')
            ->get();

       

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[],
            'pembelians' => $pembelians 
        ],200);

    }

    
}
