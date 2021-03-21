<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jadwal;

class JadwalController extends Controller
{
    public function getJadwal(Request $request){
        $jadwals = Jadwal::whereDate('tanggal',$request->date)
                            ->where('id_asal_pelabuhan',$request->id_asal_pelabuhan)
                            ->where('id_tujuan_pelabuhan',$request->id_tujuan_pelabuhan)
                            ->get(['id','id_asal_pelabuhan','id_tujuan_pelabuhan','waktu_sampai','waktu_berangkat','id_speedboat','harga']);
        
        foreach ($jadwals as $index => $jadwal) {
            $pelabuhan_asal = $jadwal->getPelabuhanAsal();
            $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
            $speedboat = $jadwal->getBoat();
            $pemesanan_saat_ini = $jadwal->getTotalPembelianSaatini();
            $sisa = $speedboat->kapasitas - $pemesanan_saat_ini;

            $jadwals[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
            $jadwals[$index]->pelabuhan_asal_kode = $pelabuhan_asal->kode_pelabuhan;
            
            $jadwals[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
            $jadwals[$index]->pelabuhan_tujuan_kode = $pelabuhan_tujuan->kode_pelabuhan;
         
            $jadwals[$index]->nama_speedboat = $speedboat->nama_speedboat;
            $jadwals[$index]->kapasitas = $speedboat->kapasitas;
            $jadwals[$index]->pemesanan_saat_ini = $pemesanan_saat_ini;
            $jadwals[$index]->sisa = $sisa;
            $jadwals[$index]->sisa = $sisa;
            $jadwals[$index]->deskripsi_boat = $speedboat->deskripsi;
            $jadwals[$index]->foto_boat = $speedboat->foto;
            $jadwals[$index]->contact_service = $speedboat->contact_service;
            $jadwals[$index]->tanggal_beroperasi = $speedboat->tanggal_beroperasi;
        }

        if($jadwals != null){
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil mendapatkan jadwal',
                'error' => (Object)[],
                'jadwal' => $jadwals
            ],200);
        }else{
            return response()->json([
                'response_code' => 401,
                'status' => 'success',
                'message' => 'gagal terjadi kesalahan',
                'error' => (Object)[],
                'jadwal' => []
            ],200);
        }
        
    }
}
