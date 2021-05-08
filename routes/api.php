<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login','API\AuthController@login');
Route::post('/register','API\AuthController@register');
Route::get('/failure','API\AuthController@failureMethod')->name('failure');


// LUPA PASSWORD TANPA AUTH
    // MENGGUNAKAN EMAIL
        // REQUEST EMAIL VERIFIKASI SEBELUM GANTI PASSORD
            Route::post('/requestemailcode','API\LupaPasswordController@verifikasiEmailLupaPassword');
        // AKHIR

        // VERIFIKASI CODE DARI USER
            Route::post('/verifikasilupapasswordemail','API\LupaPasswordController@verifikasiCodeEmail');
        // AKHIR

        // MENGUBAH PASSWORD
            Route::post('/ubahpasswordmenggunakanemail','API\LupaPasswordController@changePasswordWithEmail');
        // AKHIR
    // AKHIR

    // MENGGUNAKAN TELEGRAM
        // REQUEST PASSWORD BARU DENGAN TELEGRAM
            Route::post('/requesttelegramnewpass','API\LupaPasswordController@telegramLupaPassword');
        // AKHIR
    // AKHIR
// AKHIR

Route::group(['middleware' => 'auth:api'],function(){
    // USER
        // READ USERPROFILE
            Route::post('/userprofile','API\UserController@detail');
        // AKHIR

        // EDIT PROFILE
            Route::post('/user/editProfile', 'API\UserController@editProfile');
        // AKHIR

        // EDIT PASSWORD
            Route::post('/user/editPassword', 'API\UserController@editPassword');
        // AKHIR

        // EDIT PIN
            Route::post('/user/editPin', 'API\UserController@editPin');
        // AKHIR

        // ADD PIN
            Route::post('/user/addPin', 'API\UserController@addPin');
        // AKHIR

        // LOG OUT
            Route::post('/user/logout', 'API\UserController@logout');
        //AKHIR
    // AKHIR

    // BERITA PELABUHAN
        // READ BERITA PELABUHAN
            Route::post('/readberitapelabuhan','API\BeritaPelabuhanController@getAllBeritaPelabuhan');
        // AKHITR
    // AKHIR

    // BERITA ESPEED
        // READ BERITA ESPEED
            Route::post('/readberitaespeed','API\BeritaEspeedController@getAllBeritaEpseed');
        // AKHITR
    // AKHIR

    // PELABUHAN
        // READ PELABUHAN
            Route::post('/readpelabuhan','API\PelabuhanController@readAllPelabuhan');
        // AKHIR

        // READ GOLONGAN PELABUHAN
            Route::post('/readgologan','API\GolonganController@getGolonganByPelabuhanId');
        // AKHIR
    // AKHIR

    // PROSES TRANSAKSI
        // GET JADWAL
            Route::post('/getjadwal','API\JadwalController@getJadwal');
        // AKHIR

        // GET METODE PEMBAYARAN
            Route::post('/getmetodepembayaran','API\PemesananController@showMetodePembayaran');
        // AKHIR

        // POST PEMESANAN
            Route::post('/postpemesanan','API\PemesananController@createPemesanan');
        // AKHJIR

        // POST BUKTI PEMBAYARAN
            Route::post('/postbuktipembayaran','API\PembelianController@uploadButkiPembelian');
        // AKHIR

        // POST BATALKAN PEMBELIAN
            Route::post('/batalkanpembelian','API\PembelianController@batalkanPembelian');
        // AKHIR

        // POST STATUS PEMBELIAN
            Route::post('/setpembelianstatus','API\PembelianController@setpembelianstatus');
        // AKHIR
    // AKHIR

    // RIWAYAT TRANSAKSI
        //GET TRANSAKSI LIST
            Route::post('/getPembelian', 'API\PembelianController@getpembelian');
        //AKHIR
        //GET DETAIL TRANSAKSI
            Route::post('/getDetailPembelian', 'API\PembelianController@getdetailpembelian');
        //AKHIR
    // AKHIR

    // REVIEW TRANSAKSI
        // SET REVIEW
            Route::post('/setReview', 'API\ReviewController@setReview');
        // AKHIR
    // AKHIR

    // POIN REWARD
        // GET LIST POIN
        Route::post('/getPoin', 'API\RewardController@getPoin');
        // AKHIR

        // GET LIST Reward
        Route::post('/getReward', 'API\RewardController@getReward');
        // AKHIR

        // TUKAR POINT
        Route::post('/tukarReward', 'API\RewardController@tukarReward');
        // AKHIR

        // RIWAYAT POINT
        Route::post('/getRiwayatReward', 'API\RewardController@getRiwayatReward');
        //AKHIR

        // TERIMA REWARD
        Route::post('/terimaReward', 'API\RewardController@terimaReward');
        //AKHIR
    // AKHIR
});
