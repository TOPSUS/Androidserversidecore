<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\UserNotification;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class UserNotificationController extends Controller
{
    /**
     * METHOD UNTUK MENDAPATKAN NOTIFIKASI 1 BULAN TERAKHIR
     */
    public function getAllNotification(Request $request){
        // AMBIL DATA USER
        $user = Auth::user();

        // AMBIL NOTIFIKASI USER LIMIT 20 NOTIFIKASI TERAKHIR
        $user_notification = UserNotification::where('user_id',$user->id)->limit(20)->orderBy('id','DESC')
                                    ->get([
                                        'id as id_server_notification',
                                        'title',
                                        'body as message',
                                        'notification_by',
                                        'status',
                                        'type',
                                        'created_at']);

        // RETURN SEMUA NOTIFICATION
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'notifikasi didapatkan',
            'error' => (object)[],
            'notifications' => $user_notification
        ], 200);
    }
}