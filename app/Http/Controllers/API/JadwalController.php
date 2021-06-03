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
            'id_golongan' => 'nullable|numeric|exists:tb_golongan,id',
            'jumlah_penumpang' => 'required|numeric',
            'tipe_kapal' => 'required|in:speedboat,feri'
        ]);

        if($validator->fails()){
            return response()->json([
                'response_code' => 400,
                'status' => 'failure',
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
                            ->orderBy('waktu_berangkat',"DESC")
                            ->get();

        // JADWAL YANG DICARI ADALAH JADWAL SESUAI tipe_kapal / TIPE KAPAL DAN BATAS WAKTU 2 JAM
        if($request->id_golongan == null){
            foreach ($jadwals as $index => $jadwal) {
                    $carbon_jadwal = Carbon::parse($request->date." ".$jadwal->waktu_berangkat);
                    

                    $pelabuhan_asal = $jadwal->getPelabuhanAsal();
                    $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
                    $speedboat = $jadwal->getKapal()->first();
                    $pemesanan_saat_ini = $jadwal->getTotalPembelianSaatini($request->date);
                    $sisa = ($speedboat->kapasitas - $pemesanan_saat_ini);

                    if($sisa <= 0){
                        $jadwals->forget($index)->values();
                        continue;
                    }
                    else if(($carbon_jadwal->diffInMilliseconds($limit_waktu,false) > 0)){
                        $jadwals[$index]->isOrderable = false;
                    }else{
                        $jadwals[$index]->isOrderable = true;
                    }

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
                    $string_waktu_berangkat = $request->date." ".$jadwal->waktu_berangkat;
                    $jadwals[$index]->waktu_sampai = Carbon::createFromFormat("Y-m-d H:i:s",$string_waktu_berangkat)
                                                        ->addMinutes($jadwal->estimasi_waktu)->format("H:i:s");
            }
        }else{     
                foreach ($jadwals as $index => $jadwal) {
                    $max_jumlah_golongan = $jadwal->getKapal()->first()->getDetailGolongan()->where('id_golongan',1)->first()->jumlah;
                    $jumlah_pembelian_golongan_saat_ini = $jadwal->getDetailJadwal()->first()->getPembelian()->whereDate('tanggal',$request->date)->where('status','terkonfirmasi')->count();
                    $sisa = $max_jumlah_golongan - $jumlah_pembelian_golongan_saat_ini;

                    $pelabuhan_asal = $jadwal->getPelabuhanAsal();
                    $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
                    $speedboat = $jadwal->getKapal()->first();
                    

                    $carbon_jadwal = Carbon::parse($request->date." ".$jadwal->waktu_berangkat);
                    if($sisa <= 0){
                        $jadwals[$index]->isOrderable = false;
                        $jadwals[$index]->status = "KAPASITAS_FULL";
                    }
                    else if(($carbon_jadwal->diffInMilliseconds($limit_waktu,false) > 0)){
                        $jadwals[$index]->isOrderable = false;
                        $jadwals[$index]->status = "SUDAH_BERANGKAT";
                    }else{
                        $jadwals[$index]->isOrderable = true;
                        $jadwals[$index]->status = "BISA_DIPESAN";
                    }

                    $jadwals[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_asal_kode = $pelabuhan_asal->kode_pelabuhan;
                    
                    $jadwals[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_tujuan_kode = $pelabuhan_tujuan->kode_pelabuhan;
                
                    $jadwals[$index]->nama_speedboat = $speedboat->nama_kapal;
                    $jadwals[$index]->kapasitas = $max_jumlah_golongan;
                    $jadwals[$index]->pemesanan_saat_ini = $jumlah_pembelian_golongan_saat_ini;
                    $jadwals[$index]->sisa = $sisa;
                    $jadwals[$index]->deskripsi_boat = $max_jumlah_golongan;
                    $jadwals[$index]->foto_boat = $speedboat->foto;
                    $jadwals[$index]->contact_service = $speedboat->contact_service;
                    $jadwals[$index]->tanggal_beroperasi = $speedboat->tanggal_beroperasi;

                    // BUAT WAKTU SAMPAI DENGAN CARBON;
                    $string_waktu_berangkat = $request->date." ".$jadwal->waktu_berangkat;
                    $jadwals[$index]->waktu_sampai = Carbon::createFromFormat("Y-m-d H:i:s",$string_waktu_berangkat)
                                                        ->addMinutes($jadwal->estimasi_waktu)->format("H:i:s");
                }
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