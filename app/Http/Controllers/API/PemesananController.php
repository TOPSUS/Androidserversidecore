<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Pembelian;
use App\DetailPembelian;
use App\Card;
use App\Jadwal;
use App\MetodePembayaran;
use App\Golongan;
use App\Http\Helper\NotificationHelper;
use App\Http\Helper\MyDayNameTranslater;
use Carbon\Carbon;
use DB;

class PemesananController extends Controller
{
    /**
     * METODE YANG DIPANGGIL SAAT USER TELAH METODE PEMBAYARAN YANG AKAN DIGUNAKAN
     * PADA LAMAN METODE PEMBAYARAN MOBILE
     */
    public function createPemesanan(Request $request){

        // VALIDATOR REQUEST
            $penumpang_decode = \json_decode($request->penumpang);

            $validator = Validator::make([
                'id_pemesan' => $request->id_pemesan,
                'id_jadwal' => $request->id_jadwal,
                'id_metode_pembayaran' => $request->id_metode_pembayaran,
                'penumpang' => $penumpang_decode,
                'tanggal_berangkat' => $request->tanggal_berangkat,
                'tipe_kapal' => $request->tipe_kapal,
                'id_golongan' => $request->id_golongan,
                'nomor_polisi' => $request->nomor_polisi
            ],[
                'id_pemesan' => 'required|numeric',
                'id_jadwal' => 'required|numeric',
                'id_metode_pembayaran' => 'required|numeric',
                'penumpang' => 'required|array',
                'tanggal_berangkat' => 'required|date|after:yesterday',
                'tipe_kapal' => 'required|in:speedboat,feri',
                'id_golongan' => 'nullable|numeric',
                'nomor_polisi' => 'nullable'
            ]);

            if($validator->fails()){
                return response()->json([
                    'response_code' => 402,
                    'status' => 'failure',
                    'message' => 'terdapat format penulisan parameter yang salah',
                    'error' => $validator->errors(),
                ],200);
            }
        // AKHIR

        // MAIN LOGIC BUAT SEBUAH PEMESANAN
            // CEK APAKAN JADWAL MASIH TERSEDIA UNTUK SEMUA PENUMPANG
                $jadwal = Jadwal::find($request->id_jadwal);
                
                if($jadwal == null){
                    return response()->json([
                        'response_code' => 401,
                        'status' => 'failure',
                        'message' => 'Tidak ditemukan id_jadwal yang dimaksud',
                        'error' => (Object)[],
                    ],200);
                }
                
                $speedboat = $jadwal->getKapal()->where('tipe_kapal',$request->tipe_kapal)->first();
                
                $nama_hari_berangkat = MyDayNameTranslater::changeDayName(Carbon::parse($request->tanggal_berangkat)->dayName);

                $detai_jadwal = $jadwal->getDetailJadwal()->where('hari',$nama_hari_berangkat)->first();

                if($detai_jadwal == null){
                    return response()->json([
                        'response_code' => 400,
                        'status' => 'failure',
                        'message' => 'Tidak ditemukan id_detail_jadwal yang dimaksud',
                        'error' => (Object)[],
                    ],200);
                }

                $total_pembelian_saat_ini = $jadwal->getTotalPembelianSaatini($request->tanggal_berangkat);
                
                // CHECK APAKAH ADA KAPASITAS
                if(($speedboat->kapasitas - $total_pembelian_saat_ini) >= count($penumpang_decode)){
                    
                    // SIMPAN KE DALAM TABLE PEMBELIAN
                        $pembelian = new Pembelian;
                        $pembelian->id_jadwal = $detai_jadwal->id;
                        $pembelian->id_user = $request->id_pemesan;
                        $pembelian->id_metode_pembayaran = $request->id_metode_pembayaran;
                        $pembelian->tanggal = $request->tanggal_berangkat;
                        
                        $pembelian->status = 'menunggu pembayaran';

                        // APABILA TIPE KAPAL FERI DAN MENGGUNAKAN KENDARAAN
                        if($request->tipe_kapal == 'feri' && $request->id_golongan != null){
                            $golongan = Golongan::find($request->id_golongan);

                            if($golongan == null){
                                return response()->json([
                                    'response_code' => 401,
                                    'status' => 'failure',
                                    'message' => 'Tidak ditemukan  golongan yang dimaksud',
                                    'error' => (Object)[],
                                ],200);
                            }

                            $pembelian->total_harga = $golongan->harga;
                            $pembelian->id_golongan = $request->id_golongan;
                            $pembelian->nomor_polisi = $request->nomor_polisi;
                        }else{
                            $pembelian->total_harga = $jadwal->harga * count($penumpang_decode);
                        }

                        $pembelian->save();
                    // AKHIR

                    // SIMPAN KE DALAM DETAIL PEMBELIAN
                        foreach ($penumpang_decode as $index => $penumpang) {

                            $statement = DB::select("SHOW TABLE STATUS LIKE 'tb_detail_pembelian'");
                            $nextId = $statement[0]->Auto_increment;

                            // MENCARI KODE CARD DENGAN ID
                            $card = Card::where('card',$penumpang->type_id_card)->first();

                            // MEMBUAT KODE TICKET
                            $kode_tiket = date('Ymd').$nextId;

                            if($card == null){
                                $card = Card::find(1);
                            }

                            $detail_pembelian = new DetailPembelian;
                            $detail_pembelian->id_pembelian = $pembelian->id;
                            $detail_pembelian->id_card = $card->id;
                            $detail_pembelian->kode_tiket = $kode_tiket;
                            $detail_pembelian->nama_pemegang_tiket = $penumpang->nama_pemegang_ticket;
                            $detail_pembelian->no_id_card = $penumpang->no_id_card;

                            // APABILA TIPE DARI JASA YANG DIMINTA ADALAH KENDARAAN MAKA PENUMPANG PERTAMA HARGA NYA SESUAI DENGAN GOLONGAN
                            if($request->tipe_kapal == 'feri' && $request->id_golongan != null){
                                $detail_pembelian->harga = 0;
                            }else{
                                $detail_pembelian->harga = $jadwal->harga;
                            }

                            $detail_pembelian->status = "Not Used";
                            $detail_pembelian->save();
                        }
                    // AKHIR

                    // BUAT NOTIFIKASI
                    $user = Auth::user();
                    NotificationHelper::createNotification($user->id,$user->fcm_token,"Pemesanan dilakukan","Pemesanan ticket dengan id ".$pembelian->id." telah berhasil dilakukan, mohon untuk segera melakukan pembayaran sebelum batas waktu yang diberikan",
                    NotificationHelper::STATUS_DELIVERED,NotificationHelper::TYPE_NORMAL,NotificationHelper::NOTIFICATION_BY_SYSTEM);

                    return response()->json([
                        'response_code' => 200,
                        'status' => 'success',
                        'message' => "berhasil melakukan pemesanan".$user->id." & ".$user->fcm_token,
                        'error' => (Object)[],
                        'pembelian' => $pembelian
                    ],200);    
                }else{

                    // SAAT KAPASITAS TIDAK CUKUP
                    return response()->json([
                        'response_code' => 402,
                        'status' => 'failure',
                        'message' => "ticket speedboat tidak mencukupi",
                        'error' => (Object)[],
                    ],200);
                }
        // AKHIR
    }

    /**
     * 
     * METHOD YANG DIGUNAKAN UNTUK MENGAMBIL METODE PEMBAYARAN
     * 
     */
    public function showMetodePembayaran(){
        // MENGAMBIL SEMUA METHOD
            $metode_pembayaras = MetodePembayaran::all();

            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => "berhasil mendapatkan semua metode pembayaran",
                'error' => (Object)[],
                'metode_pembayaran' => $metode_pembayaras
            ],200); 
        // AKHIR
    }

    // METHOD UNTUK MENAMPILKAN CARD SISTEM
    public function getAllCard(){
        $cards = Card::all();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => "berhasil mendapatkan semua card",
            'error' => (Object)[],
            'cards' => $cards
        ],200);
    }
}
