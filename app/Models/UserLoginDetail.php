<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLoginDetail extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'pgsql_master';
    protected $table = 'tbl_user_login_details';


    /**
     * | Geo Location List
     */
    public function listgeoLocation()
    {
        $data = UserLoginDetail::select(
            'tbl_tc_trackings.id',
            'tbl_tc_trackings.user_id',
            'tbl_user_details.name as user_name',
            'latitude',
            'longitude',
            DB::raw("TO_CHAR(created_at, 'DD-MM-YYYY HH12:MI:SS AM') as epoctime"),
            DB::raw("TO_CHAR(created_at, 'DD-MM-YYYY') as date"),
            DB::raw("TO_CHAR(created_at, 'HH12:MI:SS AM') as time"),
        )
            ->join('tbl_user_details', 'tbl_user_details.id', 'tbl_tc_trackings.user_id')
            ->orderbydesc('tbl_tc_trackings.id');

        return $data;
    }
}
