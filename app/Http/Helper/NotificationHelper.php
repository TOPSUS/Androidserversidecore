<?php
namespace App\Http\Helper;

use App\User;
use App\UserNotification;
use GuzzleHttp\Client;

class NotificationHelper{
    
    /**
     * METHOD STATIC YANG DAPAT DIAKSES DARI MANAPUN DI DALAM CONTROLLER
     * UNTUK MELAKUKAN PENGIRIMAN NOTIFIKASI MENGGUNAKAN FIREBASE CLOUD MESSAGING
     */
    public static function createNotification(String $fcm_token,String $title,String $message,String $status){
        $headers = [
            'Authorization' => Config("Global.FCM_SERVER_KEY"),
            'Content-Type'  => 'application/json',
        ];
        $fields = [
            'to'=> $fcm_token,
            'data' => [
            'body' => "HAI BODY",
            'title' => "HAI TITLE"
            ]
        ];

        $fields = json_encode ( $fields );
        
        $client = new Client();

        try{
            $request = $client->post(Config("Global.FCM_END_POINT"),[
                'headers' => $headers,
                "body" => $fields,
            ]);
            $response =  $request->getBody()->getContents();
            return $response;
        }
        catch (Exception $e){
            return $e;
        }

        return $response;
    }
}