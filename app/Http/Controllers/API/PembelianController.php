<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Pembelian;
use App\DetailPembelian;
use App\User;
use App\Jadwal;
use App\MetodePembayaran;

class PembelianController extends Controller
{
    public function getPembelian(Request $request){
        $user = User::find(Auth::user()->id);
        if($request->status == "menunggu pembayaran"){
            $pembelians = Pembelian::where('id_user', $user->id)->where(function($q) {
                $q->where('status', 'menunggu pembayaran')
                  ->orWhere('status', 'menunggu konfirmasi');
            })->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        }else{
            $pembelians = Pembelian::where('id_user', $user->id)->where('status', $request->status)->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        }


        foreach ($pembelians as $index => $pembelian) {
            $jadwal = $pembelian->getJadwal();
            $pelabuhan_asal = $jadwal->getPelabuhanAsal();
            $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
            $speedboat = $jadwal->getBoat();
            $waktu_asal = $jadwal->waktu_berangkat;
            $waktu_sampai = $jadwal->waktu_sampai;

            $pembelians[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
            $pembelians[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
            $pembelians[$index]->nama_speedboat = $speedboat->nama_speedboat;

            $pembelians[$index]->tanggal = $jadwal->tanggal;
            $pembelians[$index]->waktu_berangkat = $waktu_asal;
            $pembelians[$index]->waktu_sampai = $waktu_sampai;
        }

        if($pembelians != null){
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil mendapatkan pembelian',
                'error' => (Object)[],
                'pembelian' => $pembelians
            ],200);
        }else{
            return response()->json([
                'response_code' => 401,
                'status' => 'success',
                'message' => 'gagal terjadi kesalahan',
                'error' => (Object)[],
                'pembelian' => []
            ],200);
        }
    }
}