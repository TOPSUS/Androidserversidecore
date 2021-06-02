<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Http\Helper\MyDayNameTranslater;
use App\Pembelian;

class Jadwal extends Model
{
    use SoftDeletes;
    protected $table = 'tb_jadwal';

    public function getPelabuhanAsal(){
        return $this->hasOne('App\Pelabuhan','id','id_asal_pelabuhan')->withTrashed()->first();
    }

    public function getPelabuhanTujuan(){
        return $this->hasOne('App\Pelabuhan','id','id_tujuan_pelabuhan')->withTrashed()->first();
    }

    public function getBoat(){
        return $this->hasOne('App\SpeedBoat','id','id_kapal')->where('tipe_kapal','speedboat')->first();
    }

    public function getKapal(){
        return $this->hasOne('App\SpeedBoat','id','id_kapal');
    }

    public function getTotalPembelianSaatini($tanggal){

        $nama_hari = MyDayNameTranslater::changeDayName(Carbon::parse($tanggal)->dayName);
        
        $detail_jadwal = $this->getDetailJadwal()->where('hari',$nama_hari)->first();
        
        return Pembelian::where('status','terkonfirmasi')
                            ->where('id_jadwal',$detail_jadwal->id)
                            ->where('id_golongan',null)
                            ->whereDate("tanggal",$tanggal)
                            ->count();
    }

    public function getTotalPembelianGolonganSaatIni($tanggal,$id_golongan){
        $nama_hari = MyDayNameTranslater::changeDayName(Carbon::parse($tanggal)->dayName);
        $detail_jadwal = $this->getDetailJadwal()->where('hari',$nama_hari)->first();

        return Pembelian::where('status','terkonfirmasi')
                            ->where('id_jadwal',$detail_jadwal->id)
                            ->whereNull('id_golongan')
                            ->whereDate("tanggal",$tanggal)
                            ->count();
    }

    public function getDetailJadwal(){
        return $this->hasMany('App\DetailJadwal','id_jadwal','id');
    }
}
