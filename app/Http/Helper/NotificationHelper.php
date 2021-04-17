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
    public static function createNotification(int $user_id,String $fcm_token,String $title = "",String $body="",
                                                String $status = "0",String $type = "0",
                                                String $notification_by = "0"){
        // VALIASI INPUT PARAMETER
        $validator = Validator::make([$user_id,$fcm_token,$title,$body,$status,$type,$notification_by],
                        [
                            0 => 'required|numeric',
                            1 => 'required',
                            2 => 'required',
                            3 => 'required',
                            4 => 'required|numeric',
                            5 => 'required|numeric',
                            6 => 'required|numeric',
                        ]
                        );
        
        // APABILA GAGAL LANGSUNG RETURN FALSE
        if($validator->fails()){
            return false;
        }

        // BUAT NOTIFICATION TERLEBIH DAHULU DI DALAM SISTEM DATABASE
        $notification = UserNotification::create(
                        [
                            'user_id' => $user_id,
                            'title' => $title,
                            'body' => $body,
                            'notification_by' => $notification_by,
                            'status' => $status,
                            'type' => $type,
                        ]
                        );

        // PERSIAPAN PENGIRIMAN MENGGUNAKAN API FIREBASE

        // HEADER
        $headers = [
            'Authorization' => Config("Global.FCM_SERVER_KEY"),
            'Content-Type'  => 'application/json',
        ];

        // FIELDS YANG AKAN DIKIRIMKAN
        $fields = [
            'to'=> $fcm_token,
            'data' => [
                'id' => $notification->id,
                'title' => $title,
                'body' => $body,
                'status' => $status,
                'type' => $type,
                'created_at' =>  date('Y-m-d H:i:s'),
                'notification_by' => $notification_by,
            ]
        ];

        $fields = json_encode ( $fields );
        
        // MENGGUNAKAN GUZZLE CLIENT HTTP UNTUK MELAKUKAN PENGIRIMAN DATA
        $client = new Client();

        // MENGIRIM KE FIREBASE ENDPOINT
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

        // MENERIMAN RESPONSE DARI FIREBASE ENDPOINT DAN MENGUBAH JSON KE DALAM BENTUK PHP OBJECT
        $response_decode = \json_decode($response);


        // APABILA BERHASIL RETURN TRUE APABILA GAGAL FALSE
        if($response_decode->success == 1){
            return true;
        }else{
            return false;
        }
    }
}