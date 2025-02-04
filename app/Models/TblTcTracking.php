<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TblTcTracking extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];
    protected $connection = 'pgsql_master';
    protected $table = 'tbl_tc_trackings';

    /**
     * | Add Location
     */
    public function createGeoLocation($reqs)
    {
        return TblTcTracking::create($reqs);
    }


    /**
     * | Geo Location List
     */
    public function listgeoLocation()
    {
        $data = TblTcTracking::select(
            'tbl_tc_trackings.id',
            'tbl_tc_trackings.user_id',
            'users.name as user_name',
            'latitude',
            'longitude',
            DB::raw("TO_CHAR(tbl_tc_trackings.created_at, 'DD-MM-YYYY HH12:MI:SS AM') as epoctime"),
            DB::raw("TO_CHAR(tbl_tc_trackings.created_at, 'DD-MM-YYYY') as date"),
            DB::raw("TO_CHAR(tbl_tc_trackings.created_at, 'HH12:MI:SS AM') as time"),
        )
            ->join('users', 'users.id', 'tbl_tc_trackings.user_id')
            ->orderbydesc('tbl_tc_trackings.id');

        return $data;
    }
}
