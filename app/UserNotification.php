<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use dateTimeFormater;

    protected $table = "tb_user_notification";

    // ATRIBUTE YANG DAPAT DI FILLABLE
    protected $fillable = [
        'user_id', 'title', 'body','notification_by','status','type'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}

trait dateTimeFormater
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
}