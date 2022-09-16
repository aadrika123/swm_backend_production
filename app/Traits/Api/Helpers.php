<?php

namespace App\Traits\Api;
use App\Models\Demand;
use App\Models\ViewUser;

/**
 * Helpers is a Trait of helpers
 * Created On-
 * Created By-
 * 
 */
trait Helpers
{

    static function GenerateDemand($schema, $consumerId, $taxrate, $demandFrom, $userId)
    {

        $demandFrom = strtotime(date('Y-m-d', strtotime($demandFrom)));
        $demandUpto = strtotime(date('Y-m-d'));
        $demand = array();
        while ($demandFrom <= $demandUpto)
        {
            
            $payment_from=date('Y-m-d', $demandFrom);
            $payment_to=date('Y-m-t', strtotime($payment_from));
            $demandFrom = strtotime('+1 month', $demandFrom);
            $dmd = new Demand();
            $dmd->setConnection($schema);
            $dmd->consumer_id = $consumerId;
            $dmd->total_tax = $taxrate;
            $dmd->payment_from = $payment_from;
            $dmd->payment_to = $payment_to;
            $dmd->paid_status = 0;
            $dmd->user_id = $userId;
            $dmd->date_time = date("Y-m-d H:i:s");
            $dmd->demand_date = date('Y-m-d');
            $dmd->deactivate_status = 0;
            $dmd->save();
            $demand[] = $dmd;
        }
        return $demand;
    }

    static function GetUserDetails($user_id)
    {
        $user = ViewUser::where('id', $user_id)->first();
        return $user;
    }

}
