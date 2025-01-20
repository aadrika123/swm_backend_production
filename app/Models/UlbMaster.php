<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UlbMaster extends Model
{
    use HasFactory;
    protected $connection = 'pgsql_master';

    public function GetUlbsWithWard($user_id, $Ward)
    {
        if (isset($user_id)) {
            $ulbs = Self::select('ulb_ward_masters.ulb_id', 'ulb_masters.ulb_name')
                ->join('ulb_ward_masters', 'ulb_ward_masters.ulb_id', '=', 'ulb_masters.id')
                ->join('wf_ward_users', 'wf_ward_users.ward_id', '=', 'ulb_ward_masters.id')
                ->where('wf_ward_users.user_id', $user_id)
                ->where('wf_ward_users.is_suspended', false)
                ->groupBy(['ulb_masters.ulb_name', 'ulb_ward_masters.ulb_id'])
                ->get();

            $ulbarr = array();
            if ($ulbs) {
                foreach ($ulbs as $u) {
                    $val['ulbId'] = $u->ulb_id;
                    $val['ulbName'] = $u->ulb_name;
                    //$val['ulb'] = $u->ulb;
                    $val['wards'] = $this->GetAllWard($u->ulb_id, $user_id, $Ward);
                    $ulbarr[] = $val;
                }
            }

            return $ulbarr;
        }
    }

    static function GetAllWard($ulb_id, $user_id, $Ward)
    {
        if (isset($user_id)) {
            // Define the SQL query to retrieve ulb_id and ulb_name
            $sql = "SELECT u.id as ulb_id, um.ulb_name,w.ward_id
                    FROM wf_ward_users w
                    JOIN users u ON w.user_id = u.id
                    JOIN ulb_masters um ON u.ulb_id = um.id
                    WHERE w.user_id = $user_id
                      AND w.is_suspended = false
                      AND u.suspended = false
                    GROUP BY u.id, um.ulb_name,w.ward_id";
    
            // Execute the SQL query
            $ulbs = DB::select($sql);
    
            $wardarr = array();
            if ($ulbs) {
                foreach ($ulbs as $u) {
                    // Retrieve the ward name based on ward_id
                    $getward = $Ward->where('id', $u->ward_id)->first();
                    if ($getward) {
                        $wardarr[] = $getward->ward_name;
                    }
                }
            }
            return $wardarr;
        }
    }
}    
