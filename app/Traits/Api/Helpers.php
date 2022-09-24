<?php

namespace App\Traits\Api;
use App\Models\Demand;
use App\Models\ViewUser;
use App\Models\Ulb;
use Illuminate\Support\Facades\DB;

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

    static function GetMonthlyFee($responseId, $type)
    {
        $schema = 'db_ranchi';
        $response = array();
        if(isset($responseId) && $type == 'Consumer')
        {
            
            $dmd = Demand::where('consumer_id', $responseId)
                ->where('paid_status', 1)
                ->where('deactivate_status', 0)
                ->orderby('id', 'desc')
                ->first();
            
            $total_tax = ($dmd)?$dmd->total_tax:'0.00';
            $payUpto = ($dmd)?date('d-m-Y', strtotime($dmd->payment_to)):'';
        }
        
        if(isset($responseId) && $type == 'Apartment')
        {
            $sql = "SELECT d.consumer_id,max(d.total_tax) as total_tax,max(d.payment_to) as payment_to FROM tbl_demand d 
            join tbl_consumer c on d.consumer_id=c.id
            where c.apt_mstr_id=".$responseId." and d.paid_status=1 and d.deactivate_status=0 group by d.consumer_id";
            
            $dmds = DB::connection($schema)->select($sql);
            $total_tax = 0.00;
            $payUpto = '';
            foreach($dmds as $d)
            {
                
                if($dmds){
                    $total_tax += $d->total_tax;
                    $payUpto = date('d-m-Y', strtotime($d->payment_to));
                }
            }
        }
        
        $response['monthlyFee'] = $total_tax;
        $response['paymentTill'] = $payUpto;
        return $response;
    }

    static function GetDemand($responseId, $type)
    {
        $schema = 'db_ranchi';
        $response = array();
        if(isset($responseId) && $type == 'Consumer')
        {
            
            $dmds = Demand::where('consumer_id', $responseId)
                ->where('paid_status', 0)
                ->where('deactivate_status', 0)
                ->orderby('id', 'desc')
                ->get();
            
            
            $total_tax = 0.00;
            $payUpto = '';
            foreach($dmds as $d)
            {
                $total_tax += $d->total_tax;
                $payUpto = date('d-m-Y', strtotime($d->payment_to));
            }    

        }
        
        if(isset($responseId) && $type == 'Apartment')
        {
            $sql = "SELECT d.consumer_id,d.total_tax,d.payment_to FROM tbl_demand d 
            join tbl_consumer c on d.consumer_id=c.id
            where c.apt_mstr_id=".$responseId." and d.paid_status=0 and d.deactivate_status=0 group by d.consumer_id,d.total_tax,d.payment_to";
            
            $dmds = DB::connection($schema)->select($sql);
            $total_tax = 0.00;
            $payUpto = '';
            foreach($dmds as $d)
            {
                
                if($dmds){
                    $total_tax += $d->total_tax;
                    $payUpto = date('d-m-Y', strtotime($d->payment_to));
                }
            }
        }
        
        $response['demandAmt'] = $total_tax;
        $response['demandUpto'] = $payUpto;
        return $response;
    }

    static function GetUlbs($user_id)
    {
        if(isset($user_id))
        {
            $sql = "SELECT w.ulb_id,ulb_name,ulb FROM tbl_user_ward w
            JOIN tbl_ulb_list u on w.ulb_id=u.id
            WHERE user_id=".$user_id. " and w.stts=1 group by w.ulb_id, ulb_name,ulb";
            
            $ulbs = DB::select($sql);
            $ulbarr = array();
            if($ulbs)
            {
                foreach($ulbs as $u)
                {
                    $val['ulbId'] = $u->ulb_id;
                    $val['ulbName'] = $u->ulb_name;
                    $val['ulb'] = $u->ulb;
                    $ulbarr[] = $val;
                }
                
            }
            return $ulbarr;
        }
    }

    static function GetSchema($ulb_id)
    {
        if(isset($ulb_id))
        {
            $ulb = Ulb::find($ulb_id);
            return $ulb->db_name;
        }
    }

}
