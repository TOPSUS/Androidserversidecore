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
use Carbon\Carbon;

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
        // $pembelians = DB::table('tb_pembelian')
        //     ->join('tb_jadwal', 'tb_pembelian.id_jadwal', '=', 'tb_jadwal.id')
        //     ->join('tb_kapal', 'tb_jadwal.id_kapal', '=', 'tb_kapal.id')
        //     ->select('id_kapal', 'nama_kapal', DB::raw('SUM(tb_pembelian.poin) as total_poin'))
        //     ->where('id_user', $user->id)
        //     ->where('id_golongan', NULL)
        //     ->where('status', 'digunakan')
        //     ->groupBy('tb_jadwal.id_kapal')
        //     ->get();

        $pembelians = DB::table('tb_speedboat_point')
        ->join('tb_kapal', 'tb_speedboat_point.id_speedboat', '=', 'tb_kapal.id')
        ->select('id_speedboat as id_kapal', 'nama_kapal', 'point as total_poin')
        ->where('id_user', $user->id)
        ->get();

       

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[],
            'pembelians' => $pembelians 
        ],200);

    }

    public function getReward(Request $request){
        $user = User::find(Auth::user()->id);

        if($user == null){
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'tidak ada user yang dimaksud',
                'error' => (Object)[],
            ],200);
        }

        $kapal = DB::table('tb_speedboat_point')
        ->join('tb_kapal', 'tb_speedboat_point.id_speedboat', '=', 'tb_kapal.id')
        ->select('id_speedboat as id_kapal', 'nama_kapal', 'point as total_poin')
        ->where('id_user', $user->id)
        ->where('id_speedboat', $request->id)
        ->first();

        $rewards = Reward::where('id_speedboat', $request->id)->get(['id', 'id_speedboat', 'reward', 'berlaku', 'minimal_point', 'foto']);

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[],
            'id_kapal' => $kapal->id_kapal,
            'nama_kapal' => $kapal->nama_kapal,
            'total_poin' => $kapal->total_poin,
            'rewards' => $rewards 
        ],200);
    }

    public function tukarReward(Request $request){
        $user = User::find(Auth::user()->id);

        if($user == null){
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'tidak ada user yang dimaksud',
                'error' => (Object)[],
            ],200);
        }

        $reward = DB::table('tb_reward_speedboat')
        ->where('id', $request->id)
        ->first();

        $point = DB::table('tb_speedboat_point')
        ->where('id_user', $user->id)
        ->where('id_speedboat', $reward->id_speedboat)
        ->first();

        DB::table('tb_detail_reward')->insert([
            [
                'id_speedboat_reward' => $reward->id, 
                'alamat' => $request->alamat,
                'nama_penerima' => $request->nama,
                'nomor_telepon' => $request->telepon,
                'status' =>  'menunggu konfirmasi',
                'created_at' =>  Carbon::now()
            ]
        ]);

        DB::table('tb_speedboat_point')
        ->where('id', $point->id)
        ->update(['point' => $point->point - 30]);

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => ' berhasil dilakukan',
            'error' => (Object)[],
            'haha' => $point
        ],200);
    }

    
}
