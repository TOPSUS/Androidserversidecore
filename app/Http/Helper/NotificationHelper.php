<?php
namespace App\Http\Helper;

use App\User;
use App\UserNotification;
use GuzzleHttp\Client;

use Validator;

class NotificationHelper{
    // TYPE YANG TERSEDIA
    const STATUS_DELIVERED = 0;
    const STATUS_HISTORY = 1;
    const STATUS_DELETED = 2;

    // TYPE YANG TERSEDIA
    const TYPE_NORMAL = 0;
    const TYPE_SUKSES = 1;
    const TYPE_WARNING = 2;
    const TYPE_DANGER = 3;
    const TYPE_SISTEM = 4;

    // NOTIFICATION_BY YANG TERSEDIA
    const NOTIFICATION_BY_SYSTEM = 0;
    const NOTIFICATION_BY_ADMIN = 1;
    
    /**
     * METHOD STATIC YANG DAPAT DIAKSES DARI MANAPUN DI DALAM CONTROLLER
     * UNTUK MELAKUKAN PENGIRIMAN NOTIFIKASI MENGGUNAKAN FIREBASE CLOUD MESSAGING
     */
    public static function createNotification(int $user_id,$fcm_token,String $title = "",String $body="",
                                                int $status = 0,int $type = 0,
                                                int $notification_by = 0){
        // VALIASI INPUT PARAMETER
        $validator = Validator::make([$user_id,$fcm_token,$title,$body,$status,$type,$notification_by],
                        [
                            0 => 'required|integer',
                            1 => 'nullable',
                            2 => 'required|max:100',
                            3 => 'required|max:500',
                            4 => 'required|integer|between:0,2',
                            5 => 'required|integer|between:0,4',
                            6 => 'required|integer|between:0,1',
                        ]
                        );
        
        // APABILA GAGAL LANGSUNG RETURN FALSE
        if($validator->fails()){
            return false;
        }

        // CHECK APAKAH FCM_TOKEN KOSONG, KALAU KOSONG UBAH KE BENTUK STRING EMPTY ""
        if($fcm_token == null){
            $fcm_token = "";
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
        }
        catch (Exception $e){
            return false;
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