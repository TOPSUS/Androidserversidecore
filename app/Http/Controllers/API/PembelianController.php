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
use App\Http\Helper\NotificationHelper;

class PembelianController extends Controller
{
    //GET PEMBELIAN LIST
    public function getPembelian(Request $request)
    {
        $user = User::find(Auth::user()->id);
        if ($request->status == "menunggu pembayaran") {
            $pembelians = Pembelian::where('id_user', $user->id)->where(function ($q) {
                $q->where('status', 'menunggu pembayaran')
                    ->orWhere('status', 'menunggu konfirmasi');
            })->orderBy('id', 'DESC')->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        } else if ($request->status == "terkonfirmasi") {
            $pembelians = Pembelian::where('id_user', $user->id)->where('status', $request->status)->orderBy('id', 'DESC')->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        } else if ($request->status == "done") {
            $pembelians = Pembelian::where('id_user', $user->id)->where(function ($q) {
                $q->where('status', 'digunakan')
                    ->orWhere('status', 'dibatalkan')
                    ->orWhere('status', 'expired');
            })->orderBy('id', 'DESC')->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        } else if($request->status == "digunakan"){
            $pembelians = Pembelian::where('id_user', $user->id)->where('status', $request->status)->orderBy('id', 'DESC')->get(
                ['id', 'id_jadwal', 'id_user', 'tanggal', 'total_harga', 'status']
            );
        }


        foreach ($pembelians as $index => $pembelian) {
            $jadwal = $pembelian->getJadwal();
            $pelabuhan_asal = $jadwal->getPelabuhanAsal();
            $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan();
            $speedboat = $jadwal->getKapal()->withTrashed()->first();
            $waktu_asal = $jadwal->waktu_berangkat;
            $review = $pembelian->getReview();
            if($review==NULL){
                $review = 0;
            }else if($review != NULL){
                $review = $review->score;
            }
            $pembelians[$index]->pelabuhan_asal_nama = $pelabuhan_asal->nama_pelabuhan;
            $pembelians[$index]->pelabuhan_tujuan_nama = $pelabuhan_tujuan->nama_pelabuhan;
            $pembelians[$index]->nama_speedboat = $speedboat->nama_kapal;
            $pembelians[$index]->review = $review;
            
            $pembelians[$index]->waktu_berangkat = $waktu_asal;

            $pembelians[$index]->waktu_sampai = Carbon::createFromFormat("H:i:s", $pembelian->waktu_berangkat)
                ->addMinutes($jadwal->estimasi_waktu)->format("H:i:s");
        }

        if ($pembelians != null) {
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil mendapatkan pembelian',
                'error' => (object)[],
                'pembelian' => $pembelians
            ], 200);
        } else {
            return response()->json([
                'response_code' => 401,
                'status' => 'success',
                'message' => 'gagal terjadi kesalahan',
                'error' => (object)[],
                'pembelian' => []
            ], 200);
        }
    }

    public function getdetailpembelian(Request $request)
    {
        //GET USER
        $user = User::find(Auth::user()->id);

        //GET PEMBELIAN
        $pembelian = Pembelian::where('id', $request->id)->first();
        //getSisaWaktu
        $sisaWaktu = $pembelian->created_at->format('Y-m-d H:i:s');
        $sisaWaktu = Carbon::createFromFormat('Y-m-d H:i:s', $sisaWaktu)
            ->addMinutes(60)->format('Y-m-d H:i:s');
        $sisaWaktu = Carbon::now()->diffInMilliseconds($sisaWaktu, false);
        if ($pembelian->status != "menunggu pembayaran") {
            $sisaWaktu = 0;
        }
        if ((int)$sisaWaktu < 60000 && $pembelian->status == "menunggu pembayaran") {
            $pembelian->status = "expired";
            $pembelian->save();
            $pembelian = Pembelian::where('id', $request->id)->first();
            $sisaWaktu = 0;
        }

        //GET JADWAL PEMBELIAN, PELABUHAN, WAKTU, KAPAL
        $jadwal = $pembelian->getJadwal();
        $speedboat = $jadwal->getKapal()->withTrashed()->first()->nama_kapal;
        $tanggal = $jadwal->tanggal;
        $harga = $pembelian->total_harga;
        $pelabuhan_asal = $jadwal->getPelabuhanAsal()->nama_pelabuhan;
        $pelabuhan_tujuan = $jadwal->getPelabuhanTujuan()->nama_pelabuhan;
        $waktu_berangkat = $jadwal->waktu_berangkat;
        $waktu_sampai = Carbon::createFromFormat("H:i:s", $waktu_berangkat)
            ->addMinutes($jadwal->estimasi_waktu)->format("H:i:s");
        $pembayaran = $pembelian->getPembayaran();
        $metode_pembayaran = $pembayaran->nama_metode;
        $rekening = $pembayaran->nomor_rekening;
        $logo_metode = $pembayaran->logo_metode;


        $status_transaksi = $pembelian->status;
        $sisa_waktu = $sisaWaktu;
        $nama_pemesan = $user->nama;
        $email_pemesan = $user->email;
        $telepon_pemesan = $user->nohp;

        //CEK TIKET
        if($pembelian->file_tiket == NULL){
            $tiket = "NOPE";
        }else if($pembelian->file_tiket != NULL){
            $tiket = $pembelian->file_tiket;
        }

        //CEK BUKTI
        if($pembelian->bukti == NULL){
            $bukti = "NOPE";
        }else if($pembelian->bukti != NULL){
            $bukti = $pembelian->bukti;
        }


        //CEK NOPOL
        if($pembelian->nomor_polisi == NULL){
            $nomor_polisi = "NOPE";
        }else if($pembelian->nomor_polisi != NULL){
            $nomor_polisi = $pembelian->nomor_polisi;
        }

        //CEK GOLONGAN
        if($pembelian->id_golongan == NULL){
            $golongan = "NOPE";
            $harga_golongan = 0;
        }else if($pembelian->id_golongan != NULL){
            $golongan = $pembelian->getGolongan();
            $golongan = $golongan->golongan;
            $harga_golongan = $pembelian->getGolongan();
            $harga_golongan = $harga_golongan->harga;
        }

        //CEK REVIEW
        $review_kapal = $pembelian->getReview();
        if($review_kapal==NULL){
            $rating = 0;
            $review = "";
        }else if($review_kapal != NULL){
            $rating = $review_kapal->score;
            $review = $review_kapal->review;
        }



        $penumpangs = DetailPembelian::where('id_pembelian', $request->id)->get(['nama_pemegang_tiket', 'id_card', 'no_id_card']);
        foreach ($penumpangs as $index => $penumpang) {
            $nama_penumpang = $penumpang->nama_pemegang_tiket;
            $id_card = $penumpang->getCard()->card;
            $card = $penumpang->no_id_card;


            $penumpangs[$index]->id_card = $id_card;
            $penumpangs[$index]->no_id_card = $card;
        }

        if ($pembelian != null) {
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'berhasil mendapatkan pembelian',
                'error' => (object)[],
                'kapal' => $speedboat,
                'tanggal' => $tanggal,
                'harga' => $harga,
                'pelabuhan_asal' => $pelabuhan_asal,
                'pelabuhan_tujuan' => $pelabuhan_tujuan,
                'waktu_berangkat' => $waktu_berangkat,
                'waktu_sampai' => $waktu_sampai,
                'status_transaksi' => $status_transaksi,
                'sisa_waktu' => $sisa_waktu,
                'nama_pemesan' => $nama_pemesan,
                'email_pemesan' => $email_pemesan,
                'telepon_pemesan' => $telepon_pemesan,
                'tiket' => $tiket,
                'bukti' => $bukti,
                'metode_pembayaran' => $metode_pembayaran,
                'rekening' => $rekening,
                'logo_metode' => $logo_metode,
                'nomor_polisi' => $nomor_polisi,
                'golongan' => $golongan,
                'harga_golongan' => $harga_golongan,
                'rating' => $rating,
                'review' => $review,
                'penumpang' => $penumpangs
            ], 200);
        } else {
            return response()->json([
                'response_code' => 401,
                'status' => 'success',
                'message' => 'gagal terjadi kesalahan',
                'error' => (object)[],
                'pembelian' => []
            ], 200);
        }
    }


    // MENGUPLOAD BUKTI PEMBELIAN TICKET
    public function uploadButkiPembelian(Request $request)
    {
        // LARAVEL VALIDATOR
        $validator = Validator::make($request->all(), [
            'id_pembelian' => 'required|numeric',
            'image_bukti_pembayaran' => 'required|image|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response_code' => 402,
                'status' => 'failure',
                'message' => 'Image Maksimal 5mb',
                'error' => $validator->errors(),
            ], 200);
        }
        // AKHIR

        // MAIN PROCESS UPDATE PEMBELIAN TABLE DAN SIMPAN BUKTI PEMBAYARAN
        // CARI RECORD PEMBELIAN DENGAN ID
        $pembelian = Pembelian::find($request->id_pembelian);

        // APABILA KOSONG PEMBELIANNYA MAKA AKAN DIRETURN HASIL BERIKUT
        if ($pembelian == null) {
            return response()->json([
                'response_code' => 402,
                'status' => 'failure',
                'message' => 'id yang dimaksud tidak ditemukan',
                'error' => (object)[],
            ], 200);
        }

        // CEK APAKAH BENAR TRANSAKSI INI MILIK USER YANG SEDANG LOGIN
        $user = Auth::user();

        if ($pembelian->id_user != $user->id) {
            return response()->json([
                'response_code' => 403,
                'status' => 'failure',
                'message' => 'id user yang memanggil transaksi berbeda',
                'error' => (object)[],
            ], 200);
        }

        // HAPUS FILE YANG SAMA DARI PEMBELIAN INI APABILA ADA DAN SIMPAN FILE BUKTI BARU
        Storage::delete('public_html/bukti_pembayaran/' . ($pembelian->bukti == null ? " " : $pembelian->bukti));
        $bukti_pembayaran = Storage::putFile('public_html/bukti_pembayaran', $request->file('image_bukti_pembayaran'));
        $bukti_pembayaran = basename($bukti_pembayaran);

        // SIMAPN PEMBELIAN
        $pembelian->status = 'menunggu konfirmasi';
        $pembelian->bukti = $bukti_pembayaran;
        $pembelian->update();

        // AKHIR

        // BERIKAN NOTIFIKASI BERHASIL UPLOAD BUKTI PEMBAYARAN KE USER
        NotificationHelper::createNotification($user->id, $user->fcm_token, "Upload Bukti Berhasil", "Upload bukti pembayaran dengan id " . $pembelian->id . " telah berhasil dilakukan, mohon menunggu hingga pembayaran tervalidasi",
            NotificationHelper::STATUS_DELIVERED, NotificationHelper::TYPE_SUKSES, NotificationHelper::NOTIFICATION_BY_SYSTEM);

        // RETURN BERHASIL MENYIMPAN BUKTI PEMBAYARAN
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'berhasil menyimpan file pembayaran',
            'error' => (object)[],
        ], 200);

        // AKHIR

    }

    // BATALKAN PEMBELIAN TICKET ATAU JADWAL
    public function batalkanPembelian(Request $request)
    {
        // LARAVEL VALIDATOR
        $validator = Validator::make($request->all(), [
            'id_pembelian' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response_code' => 402,
                'status' => 'failure',
                'message' => 'terdapat format yang salah',
                'error' => $validator->errors(),
            ], 200);
        }
        // AKHIR

        // MAIN LOGIC
        // MENCARI PEMBELIAN DENGAN ID YANG DIMAKSUD
        $pembelian = Pembelian::find($request->id_pembelian);

        if ($pembelian == null) {
            return response()->json([
                'response_code' => 402,
                'status' => 'failure',
                'message' => 'id pembelian tidak ditemukan',
                'error' => (object)[],
            ], 200);
        }
        // AKHIR

        // GET USER
        $user = Auth::user();

        // BERIKAN NOTIFIKASI BERHASIL UPLOAD BUKTI PEMBAYARAN KE USER
        NotificationHelper::createNotification($user->id, $user->fcm_token, "Transaksi dibatalkan", "Transaksi dengan id " . $request->id_pembelian . " telah dibatalkan.",NotificationHelper::STATUS_DELIVERED, NotificationHelper::TYPE_DANGER, NotificationHelper::NOTIFICATION_BY_SYSTEM);

        // CHECK APAKAH STATUSNYA BELUM "DIBATALKAN"
        if ($pembelian->status == 'dibatalkan') {
            return response()->json([
                'response_code' => 200,
                'status' => 'success',
                'message' => 'pembelian sudah dibatalkan sebelumnya',
                'error' => (object)[],
            ], 200);
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
            'error' => (object)[],
        ], 200);
        // AKHIR

        // AKHIR
    }

    public function setpembelianstatus(Request $request)
    {

        $pembelian = Pembelian::where('id', $request->id)->first();

        if ($pembelian == null) {
            return response()->json([
                'response_code' => 402,
                'status' => 'failure',
                'message' => 'id pembelian tidak ditemukan',
                'error' => (object)[],
            ], 200);
        }

        $pembelian->status = $request->status;
        $pembelian->save();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'pembelian status berhasil diubah',
            'error' => (object)[],
        ], 200);
    }
}
