<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\User;
use App\UserNotification;
use App\Pembelian;
use Carbon\Carbon;
use App\Http\Helper\NotificationHelper;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
         * SCHEDULE UNTUK MELAKUKAN PENGENCEKAN TICKET SETIAP MENITNYA PADA DATABASE
         * APABILA SUDAH MELEWATI WAKTU MAKA AKAN DIRUBAH MENJADI EXPIRED
         * */ 
        $schedule->call(function(){
            // GET ALL USER
            $pembelians = Pembelian::where('status','menunggu pembayaran')->get();

            // GET CURRENT TIME
            $current_time = Carbon::now();
            
            foreach ($pembelians as $index => $pembelian) {
                // CARBON PEMBELIAN CREATED_AT
                $carbon_pembelian = Carbon::parse($pembelian->created_at);
                
                // APABILA LEWAT MAKA UBAH STATUS JADI EXPIRED
                if($carbon_pembelian->diffInMilliseconds($current_time,false) > 0){
                    $pembelian->status = 'expired';
                    $pembelian->update();

                    // AMBIL USER DARI PEMBELIAN TERSEBUT
                    $user = User::find($pembelian->id_user);

                    // NOTIFIKASI KE SISI USER BAHWA TRANSAKSI TELAH EXPIRED
                    NotificationHelper::createNotification($user->id, $user->fcm_token, "Transaksi Expired",
                                                "Transaksi anda dengan ID ".$pembelian->id." telah Expired",0,3,0);
                    
                    // ECHO KE CONSOLE
                    echo "PEMBELIAN ID :".$pembelian->id." EXPIRED PADA ".$current_time->toDateTimeString()."\n";
                }

            }

        // DONE
        echo "DONE";
            
        });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
