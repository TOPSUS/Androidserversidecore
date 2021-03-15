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


Route::group(['middleware' => 'auth:api'],function(){
    // USER
        // READ USERPROFILE
            Route::post('/userprofile','API\UserController@detail');
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
    // AKHIR
}); 
