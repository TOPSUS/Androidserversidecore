<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            })->orderBy('id', 'DESC')->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        }else if($request->status == "terkonfirmasi"){
            $pembelians = Pembelian::where('id_user', $user->id)->where('status', $request->status)->orderBy('id', 'DESC')->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        }else if($request->status == "done"){
            $pembelians = Pembelian::where('id_user', $user->id)->where(function($q) {
                $q->where('status', 'digunakan')
                  ->orWhere('status', 'dibatalkan')
                  ->orWhere('status', 'expired');
            })->orderBy('id', 'DESC')->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        }

        return $pembelians;


        foreach ($pembelians as $index => $pembelian) {
            $jadwal = $pembelian->getJadwal();
            $pelabuhan_asal = $jadwal->getPelabuhanAsal();
            $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
            $speedboat = $jadwal->getKapal()->first();
            $waktu_asal = $jadwal->waktu_berangkat;

            $pembelians[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
            $pembelians[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
            $pembelians[$index]->nama_speedboat = $speedboat->nama_kapal;

            $pembelians[$index]->tanggal = $jadwal->tanggal;
            $pembelians[$index]->waktu_berangkat = $waktu_asal;
            
            $pembelians[$index]->waktu_sampai = Carbon::createFromFormat("H:i:s",$pembelian->waktu_berangkat)
                                                ->addMinutes($jadwal->estimasi_waktu)->format("H:i:s");
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

    public function getdetailpembelian(Request $request){
        //GET USER
        $user = User::find(Auth::user()->id);

        //GET PEMBELIAN
        $pembelian = Pembelian::where('id', $request->id)->first();

        //GET JADWAL PEMBELIAN, PELABUHAN, WAKTU, KAPAL
        $jadwal = $pembelian->getJadwal();

        $speedboat = $jadwal->getBoat()->nama_speedboat;
        $tanggal = $jadwal->tanggal;
        $harga = $pembelian->total_harga;
        $pelabuhan_asal = $jadwal->getPelabuhanAsal()->nama_pelabuhan;
        $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan()->nama_pelabuhan;
        $waktu_berangkat = $jadwal->waktu_berangkat;
        $waktu_sampai = Carbon::createFromFormat("H:i:s",$waktu_berangkat)
                        ->addMinutes($jadwal->estimasi_waktu)->format("H:i:s");
        $status_transaksi = $pembelian->status;
        $sisa_waktu = 3;
        $nama_pemesan = $user->nama;
        $email_pemesan = $user->email;
        $telepon_pemesan = $user->nohp;
        $tiket = "NOPE";
        $bukti = "NOPE";

        $penumpangs = DetailPembelian::where('id_pembelian', $request->id)->get(['nama_pemegang_tiket', 'id_card', 'no_id_card']);
        foreach ($penumpangs as $index => $penumpang) {
            $nama_penumpang = $penumpang->nama_pemegang_tiket;
            $id_card = $penumpang->getCard()->card;
            $card = $penumpang->no_id_card;


            $penumpangs[$index]->id_card = $id_card;
            $penumpangs[$index]->no_id_card = $card;
        }

        if($pembelian != null){
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil mendapatkan pembelian',
                'error' => (Object)[],
                'kapal' => $speedboat,
                'tanggal' => $tanggal,
                'harga' => $harga,
                'pelabuhan_asal' => $pelabuhan_asal,
                'pelabuhan_tujuan' => $pelabuhan_tujuan,
                'waktu_berangkat' => $waktu_berangkat,
                'waktu_sampai' => $waktu_sampai,
                'status_transaksi' => $status_transaksi,
                'sisa_waktu' =>$sisa_waktu,
                'nama_pemesan' => $nama_pemesan,
                'email_pemesan' => $email_pemesan,
                'telepon_pemesan' => $telepon_pemesan,
                'tiket' => $tiket,
                'bukti' => $bukti,
                'penumpang' => $penumpangs
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

    // MENGUPLOAD BUKTI PEMBELIAN TICKET
    public function uploadButkiPembelian(Request $request){
        // LARAVEL VALIDATOR
            $validator = Validator::make($request->all(),[
                'id_pembelian' => 'required|numeric',
                'image_bukti_pembayaran' => 'required|image|max:1000'
            ]);

            if($validator->fails()){
                return response()->json([
                    'response_code' => 402,
                    'status' => 'failure',
                    'message' => 'terdapat format yang salah',
                    'error' => $validator->errors(),
                ],200);
            }
        // AKHIR

        // MAIN PROCESS UPDATE PEMBELIAN TABLE DAN SIMPAN BUKTI PEMBAYARAN
            // CARI RECORD PEMBELIAN DENGAN ID
                $pembelian = Pembelian::find($request->id_pembelian);
            
                // APABILA KOSONG PEMBELIANNYA MAKA AKAN DIRETURN HASIL BERIKUT
                if($pembelian == null){
                    return response()->json([
                        'response_code' => 402,
                        'status' => 'failure',
                        'message' => 'id yang dimaksud tidak ditemukan',
                        'error' => (Object)[],
                    ],200);
                }

                // CEK APAKAH BENAR TRANSAKSI INI MILIK USER YANG SEDANG LOGIN
                $user = Auth::user();

                if($pembelian->id_user != $user->id){
                    return response()->json([
                        'response_code' => 403,
                        'status' => 'failure',
                        'message' => 'id user yang memanggil transaksi berbeda',
                        'error' => (Object)[],
                    ],200);
                }
                
                // HAPUS FILE YANG SAMA DARI PEMBELIAN INI APABILA ADA DAN SIMPAN FILE BUKTI BARU
                Storage::delete('public_html/bukti_pembayaran/'.($pembelian->bukti == null ? " " : $pembelian->bukti));
                $bukti_pembayaran = Storage::putFile('public_html/bukti_pembayaran',$request->file('image_bukti_pembayaran'));
                $bukti_pembayaran = basename($bukti_pembayaran);

                // SIMAPN PEMBELIAN
                $pembelian->status = 'menunggu konfirmasi';
                $pembelian->bukti = $bukti_pembayaran;
                $pembelian->update();

            // AKHIR
            
            // RETURN BERHASIL MENYIMPAN BUKTI PEMBAYARAN
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil menyimpan file pembayaran',
                'error' => (Object)[],
            ],200);

        // AKHIR

    }

    // BATALKAN PEMBELIAN TICKET ATAU JADWAL
    public function batalkanPembelian(Request $request){
        // LARAVEL VALIDATOR
            $validator = Validator::make($request->all(),[
                'id_pembelian' => 'required|numeric'
            ]);

            if($validator->fails()){
                return response()->json([
                    'response_code' => 402,
                    'status' => 'failure',
                    'message' => 'terdapat format yang salah',
                    'error' => $validator->errors(),
                ],200);
            }
        // AKHIR

        // MAIN LOGIC
            // MENCARI PEMBELIAN DENGAN ID YANG DIMAKSUD
                $pembelian = Pembelian::find($request->id_pembelian);

                if($pembelian == null){
                    return response()->json([
                        'response_code' => 402,
                        'status' => 'failure',
                        'message' => 'id pembelian tidak ditemukan',
                        'error' => (Object)[],
                    ],200);
                }
            // AKHIR

            // CHECK APAKAH STATUSNYA BELUM "DIBATALKAN"
                if($pembelian->status == 'dibatalkan'){
                    return response()->json([
                        'response_code' => 200,
                        'status' => 'success',
                        'message' => 'pembelian sudah dibatalkan sebelumnya',
                        'error' => (Object)[],
                    ],200);
                }

            // MENGUBAH STATUS MENJADI DIBATALKAN PEMBELIAN
                $pembelian->status = 'dibatalkan';
                $pembelian->update();
            // AKHIR
            
            // RETURN SUKSES RESPONSE
                return response()->json([
                    'response_code' => 200,
                    'status' => 'success',
                    'message' => 'pembelian berhasil dibatalkan',
                    'error' => (Object)[],
                ],200);
            // AKHIR

        // AKHIR
    }
}