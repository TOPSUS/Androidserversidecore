<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Helper\MyDayNameTranslater;
use Carbon\Carbon;
use App\Jadwal;
use App\DetailJadwal;
use App\Golongan;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

        $nama_hari_pesanan = MyDayNameTranslater::changeDayName(Carbon::create($request->date)->dayName);

        // PENCARIAN JADWAL DENGAN MODEL JADWAL
        $jadwals = Jadwal::whereHas('getKapal',function($query) use ($request){
                                $query->where('tipe_kapal',$request->tipe_kapal);
                            })
                            ->whereHas('getDetailJadwal',function($query) use ($nama_hari_pesanan){
                                $query->where('hari',$nama_hari_pesanan)->where('status','aktif');
                            })
                            ->where('id_asal_pelabuhan',$request->id_asal_pelabuhan)
                            ->where('id_tujuan_pelabuhan',$request->id_tujuan_pelabuhan)
                            ->orderBy('waktu_berangkat',"DESC")
                            ->get();

        if($request->id_golongan == null){
            foreach ($jadwals as $index => $jadwal) {

                    $carbon_jadwal = Carbon::parse($request->date." ".$jadwal->waktu_berangkat);
                    
                    $pelabuhan_asal = $jadwal->getPelabuhanAsal();
                    $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
                    $speedboat = $jadwal->getKapal()->first();
                    $detail_jadwal =  $jadwal->getDetailJadwal()->where('hari',$nama_hari_pesanan)->first();

                    try{
                        $safe_dermaga_asal = $jadwal->getDetailJadwal()->where('hari',$nama_hari_pesanan)->firstOrFail()->getDermagaAsal()->firstOrFail()->nama_dermaga;
                    }catch(ModelNotFoundException  $error){
                        $safe_dermaga_asal = "Dermaga Utama";
                    }

                    try{
                        $safe_dermaga_tujuan = $jadwal->getDetailJadwal()->where('hari',$nama_hari_pesanan)->firstOrFail()->getDermagaTujuan()->firstOrFail()->nama_dermaga;
                    }catch(ModelNotFoundException  $error){
                        $safe_dermaga_tujuan = "Dermaga Utama";
                    }
                
                    $jadwals[$index]->dermaga_asal = $safe_dermaga_asal;
                    $jadwals[$index]->dermaga_tujuan = $safe_dermaga_tujuan;

                    if((($speedboat->kapasitas - $pemesanan_saat_ini) - $request->jumlah_penumpang) <= 0){
                        $jadwals[$index]->isOrderable = false;
                        $jadwals[$index]->status = "KAPASITAS FULL / KURANG";
                    }
                    else if(($carbon_jadwal->diffInMilliseconds($limit_waktu,false) > 0)){
                        $jadwals[$index]->isOrderable = false;
                        $jadwals[$index]->status = "MELEWATI LIMIT WAKTU";
                    }else{
                        $jadwals[$index]->isOrderable = true;
                        $jadwals[$index]->status = "BISA DIPESAN";
                    }
                    
                    if($request->tipe_kapal == "speedboat"){
                        if($speedboat->harga_tiket == null){
                            $jadwals[$index]->isOrderable = false;
                            $jadwals[$index]->status = "HARGA BELUM DIATUR";
                            $jadwals[$index]->harga = 0;
                        }else{
                            $jadwals[$index]->harga = $speedboat->harga_tiket;
                        }

                        $pemesanan_saat_ini = $jadwal->getTotalPembelianSaatini($request->date);
                        $sisa = ($speedboat->kapasitas - $pemesanan_saat_ini);

                        $jadwals[$index]->kapasitas = $speedboat->kapasitas;
                        $jadwals[$index]->pemesanan_saat_ini = $pemesanan_saat_ini;
                        $jadwals[$index]->sisa = $sisa;
                    }else{
                        try{
                            $golongan_penumpang = Golongan::where('id_pelabuhan',$pelabuhan_asal->id)->where('golongan','golongan penumpang')->firstOrFail();
                            $jadwals[$index]->harga = $golongan_penumpang->harga;
                        }catch(ModelNotFoundException  $error){
                            $jadwals[$index]->isOrderable = false;
                            $jadwals[$index]->harga = 0;
                            $jadwals[$index]->status = "TIDAK SUPPORT GOLONGAN";
                        }

                        // UBAH SISA VARIABLE
                        $pemesanan_saat_ini = $jadwal->getTotalPembelianSaatini($request->date);
                        
                        $sisa = ($detail_jadwal->jumlah - $pemesanan_saat_ini);

                        $jadwals[$index]->kapasitas = $detail_jadwal->jumlah;
                        $jadwals[$index]->pemesanan_saat_ini = $pemesanan_saat_ini;
                        $jadwals[$index]->sisa = $sisa;
                    }

                    $jadwals[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_asal_kode = $pelabuhan_asal->kode_pelabuhan;
                    
                    $jadwals[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_tujuan_kode = $pelabuhan_tujuan->kode_pelabuhan;
                
                    $jadwals[$index]->nama_speedboat = $speedboat->nama_kapal;
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
                    
                    try{
                        $golongan = Golongan::findOrFail($request->id_golongan);
                        $jadwals[$index]->harga = $golongan->harga;
                    }catch(ModalNotFoundException $error){
                        $jadwals[$index]->harga = 0;
                    }
                    
                    $detail_golongan_exists = $jadwal->getKapal()->first()->getDetailGolongan()->where('id',$request->id_golongan)->first();

                    try{
                        $safe_dermaga_asal = $jadwal->getDetailJadwal()->where('hari',$nama_hari_pesanan)->firstOrFail()->getDermagaAsal()->firstOrFail()->nama_dermaga;
                    }catch(ModelNotFoundException $error){
                        $safe_dermaga_asal = "Dermaga Utama";
                    }

                    try{
                        $safe_dermaga_tujuan = $jadwal->getDetailJadwal()->where('hari',$nama_hari_pesanan)->firstOrFail()->getDermagaTujuan()->firstOrFail()->nama_dermaga;
                    }catch(ModelNotFoundException $error){
                        $safe_dermaga_tujuan = "Dermaga Utama";
                    }
                    
                    $jadwals[$index]->dermaga_asal = $safe_dermaga_asal;
                    $jadwals[$index]->dermaga_tujuan = $safe_dermaga_tujuan;
                    
                    if($detail_golongan_exists != null){
                        $max_jumlah_golongan = $jadwal->getKapal()->first()->getDetailGolongan()->where('id_golongan',$request->id_golongan)->first()->jumlah;
                    
                        $jumlah_pembelian_golongan_saat_ini = $jadwal->getDetailJadwal()->where('hari',$nama_hari_pesanan)->first()->getPembelian()->whereDate('tanggal',$request->date)->where('id_golongan',$request->id_golongan)->where('status','terkonfirmasi')->get()->count();
                        
                        $sisa = $max_jumlah_golongan - $jumlah_pembelian_golongan_saat_ini;

                        $jadwals[$index]->kapasitas = $max_jumlah_golongan;
                        $jadwals[$index]->pemesanan_saat_ini = $jumlah_pembelian_golongan_saat_ini;
                        $jadwals[$index]->sisa = $sisa;
                    }

                    $pelabuhan_asal = $jadwal->getPelabuhanAsal();
                    $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
                    $speedboat = $jadwal->getKapal()->first();
                    

                    $carbon_jadwal = Carbon::parse($request->date." ".$jadwal->waktu_berangkat);
                    if($detail_golongan_exists == null){
                        $jadwals[$index]->isOrderable = false;
                        $jadwals[$index]->status = "TIDAK SUPPORT GOLONGAN";
                        $jadwals[$index]->kapasitas = 0;
                        $jadwals[$index]->pemesanan_saat_ini = 0;
                        $jadwals[$index]->sisa = 0;
                    }
                    else if($sisa <= 0){
                        $jadwals[$index]->isOrderable = false;
                        $jadwals[$index]->status = "KAPASITAS KURANG / FULL";
                    }
                    else if(($carbon_jadwal->diffInMilliseconds($limit_waktu,false) > 0)){
                        $jadwals[$index]->isOrderable = false;
                        $jadwals[$index]->status = "MELEWATI LIMIT WAKTU";
                    }else{
                        $jadwals[$index]->isOrderable = true;
                        $jadwals[$index]->status = "BISA DIPESAN";
                    }

                    $jadwals[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_asal_kode = $pelabuhan_asal->kode_pelabuhan;
                    
                    $jadwals[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
                    $jadwals[$index]->pelabuhan_tujuan_kode = $pelabuhan_tujuan->kode_pelabuhan;
                
                    $jadwals[$index]->nama_speedboat = $speedboat->nama_kapal;
                    $jadwals[$index]->deskripsi_boat = $speedboat->deskripsi;
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