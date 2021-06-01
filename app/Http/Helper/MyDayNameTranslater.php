<?php
namespace App\Http\Helper;

class MyDayNameTranslater{
    
    public static function changeDayName(String $dayname){
        if($dayname == "Monday"){
            return "senin";
        }else if($dayname == "Tuesday"){
            return "selasa";
        }else if($dayname == "Wednesday"){
            return "rabu";
        }else if($dayname == "Thursday"){
            return "kamis";
        }else if($dayname == "Friday"){
            return "jumat";
        }else if($dayname == "Saturday"){
            return "sabtu";
        }else if($dayname == "Sunday"){
            return "minggu";
        }else{
            return false;
        }
    }
}