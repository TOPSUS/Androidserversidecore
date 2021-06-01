<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\MyDayNameTranslater;
use Carbon\Carbon;
use App\Jadwal;
use App\DetailJadwal;

class JadwalController extends Controller
{
    public function getJadwal(Request $request){
        /**
         * tipe_kapal MERUPAKAN TIPE DARI KAPAL SPEEDBOAT ATAU FERI
         */
        $validator = Validator::make($request->all(),[
            'date' => 'required|date',
            'id_asal_pelabuhan' => 'required|numeric',
            'id_tujuan_pelabuhan' => 'required|numeric',
            'tipe_kapal' => 'required|in:speedboat,feri'
        ]);

        if($validator->fails()){
            return response()->json([
                'response_code' => 400,
                'status' => 'success',
                'message' => 'gagal terjadi kesalahan',
                'error' => $validator->errors(),
                'jadwal' => []
            ],200);
        }

        // MENENTUKAN WAKTU SAAT INI DITAMBAH 2 JAM UNTUK BATAS WAKTU JADWAL YANG AKAN DI TAMPILKAN DI MOBILE
        $limit_waktu = Carbon::now()->addHours(2);

        $nama_hari_ini = MyDayNameTranslater::changeDayName(Carbon::create($request->date)->dayName);

        // PENCARIAN JADWAL DENGAN MODEL JADWAL
        $jadwals = Jadwal::whereHas('getKapal',function($query) use ($request){
                            $query->where('tipe_kapal',$request->tipe_kapal);
                            })
                            ->whereHas('getDetailJadwal',function($query) use ($nama_hari_ini){
                            $query->where('hari',$nama_hari_ini);
                            })
                            ->where('id_asal_pelabuhan',$request->id_asal_pelabuhan)
                            ->where('id_tujuan_pelabuhan',$request->id_tujuan_pelabuhan)
                            ->get(['id','id_asal_pelabuhan','id_tujuan_pelabuhan','waktu_berangkat','id_kapal','harga','estimasi_waktu']);

        return $jadwals;
        
        $jadwals = $jadwals->filter(function($jadwal) use($limit_waktu){
            $carbon_jadwal = Carbon::parse($jadwal->tanggal." ".$jadwal->waktu_berangkat);
            if($carbon_jadwal->diffInMilliseconds($limit_waktu,false) > 0){
                return false;
            }else{
                return true;
            } 
        })->values();
        
        

        // JADWAL YANG DICARI ADALAH JADWAL SESUAI tipe_kapal / TIPE KAPAL DAN BATAS WAKTU 2 JAM
            foreach ($jadwals as $index => $jadwal) {
                    $pelabuhan_asal = $jadwal->getPelabuhanAsal();
                    $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
                    $speedboat = $jadwal->getKapal()->first();
                    $pemesanan_saat_ini = $jadwal->getTotalPembelianSaatini();
                    $sisa = $speedboat->kapasitas - $pemesanan_saat_ini;

                    $jadwals[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_asal_kode = $pelabuhan_asal->kode_pelabuhan;
                    
                    $jadwals[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_tujuan_kode = $pelabuhan_tujuan->kode_pelabuhan;
                
                    $jadwals[$index]->nama_speedboat = $speedboat->nama_kapal;
                    $jadwals[$index]->kapasitas = $speedboat->kapasitas;
                    $jadwals[$index]->pemesanan_saat_ini = $pemesanan_saat_ini;
                    $jadwals[$index]->sisa = $sisa;
                    $jadwals[$index]->deskripsi_boat = $speedboat->deskripsi;
                    $jadwals[$index]->foto_boat = $speedboat->foto;
                    $jadwals[$index]->contact_service = $speedboat->contact_service;
                    $jadwals[$index]->tanggal_beroperasi = $speedboat->tanggal_beroperasi;

                    // BUAT WAKTU SAMPAI DENGAN CARBON;
                    $string_waktu_berangkat = $jadwal->tanggal." ".$jadwal->waktu_berangkat;
                    $jadwals[$index]->waktu_sampai = Carbon::createFromFormat("Y-m-d H:i:s",$string_waktu_berangkat)
                                                        ->addMinutes($jadwal->estimasi_waktu)->format("H:i:s");
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
                'status' => 'failure',
                'message' => 'gagal jadwals null',
                'error' => (Object)[],
                'jadwal' => []
            ],200);
        }
        
    }
}
