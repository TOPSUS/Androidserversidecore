<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = "tb_reward_speedboat";

    public function getBoat(){
        return $this->hasOne('App\SpeedBoat','id','id_speedboat')->first();
    }
}
