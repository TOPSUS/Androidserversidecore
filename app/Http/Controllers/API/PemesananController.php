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
                'penumpang' => $penumpang_decode
            ],[
                'id_pemesan' => 'required|numeric',
                'id_jadwal' => 'required|numeric',
                'penumpang' => 'required|array'
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
                
                $speedboat = $jadwal->getBoat();
                $total_pembelian_saat_ini = $jadwal->getTotalPembelianSaatini();
                
                if(($speedboat->kapasitas - $total_pembelian_saat_ini) >= count($penumpang_decode)){
                    
                    // SIMPAN KE DALAM TABLE PEMBELIAN
                        $pembelian = new Pembelian;
                        $pembelian->id_jadwal = $request->id_jadwal;
                        $pembelian->id_user = $request->id_pemesan;
                        $pembelian->tanggal = date('Y-m-d');
                        $pembelian->total_harga = $jadwal->harga * count($penumpang_decode);
                        $pembelian->status = 'menunggu pembayaran';
                        $pembelian->save();
                    // AKHIR

                    // SIMPAN KE DALAM DETAIL PEMBELIAN
                        foreach ($penumpang_decode as $index => $penumpang) {
                            // MENCARI KODE CARD DENGAN ID
                            $card = Card::where('card',$penumpang->type_id_card)->first();

                            // MEMBUAT KODE TICKET
                            $kode_tiket = date('Ymd').$pembelian->id;

                            if($card == null){
                                $card = Card::find(1);
                            }

                            $detail_pembelian = new DetailPembelian;
                            $detail_pembelian->id_pembelian = $pembelian->id;
                            $detail_pembelian->id_card = $card->id;
                            $detail_pembelian->kode_tiket = $kode_tiket;
                            $detail_pembelian->nama_pemegang_tiket = $penumpang->nama_pemegang_ticket;
                            $detail_pembelian->no_id_card = $penumpang->no_id_card;
                            $detail_pembelian->harga = $jadwal->harga;
                            $detail_pembelian->QRCode = "";
                            $detail_pembelian->status = "Not Used";
                            $detail_pembelian->save();
                            
                        }
                    // AKHIR

                    return response()->json([
                        'response_code' => 200,
                        'status' => 'success',
                        'message' => "berhasil melakukan pemesanan",
                        'error' => (Object)[],
                    ],200);    
                }else{

                    // SAAT KAPASITAS TIDAK CUKUP
                    return response()->json([
                        'response_code' => 402,
                        'status' => 'failure',
                        'message' => "ticket tidak speedboat tidak mencukupi",
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
}
