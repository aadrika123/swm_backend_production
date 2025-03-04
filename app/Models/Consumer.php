<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function PHPUnit\Framework\returnValueMap;

class Consumer extends Model
{
    use HasFactory;
    protected $connection;
    public $timestamps = false;
    protected $table = 'swm_consumers';

    public function __construct($data = null)
    {
        //$this->connection = Session::get('ulb');
        $this->connection = $data;

    }

    /* 
    * |.........................................................................
    * |#| All this fun is for Citizen , which retrive diffent types of data .                                   
    * |#| ----------------------------------------------------------------------
    * |#| created by : Alok                                                     
    * |.........................................................................
    */

    // 1. Get all the citizen list.
    public function getCitizenList($userId)
    {

           return Consumer::select(
                    'swm_consumers.id as consumerId',
                    'swm_consumers.ward_no as WardName',
                    'swm_consumers.consumer_no as ConsumerNumber',
                    'swm_consumers.name as ApplicantName',
                    'swm_consumers.entry_date as ApplyDate',
                    'ulb_masters.ulb_name as ULBName',
                    'swm_consumers.holding_no',
                    'swm_consumers.mobile_no',
                    'swm_consumers.address',
                    'swm_consumers.pincode',
                    'swm_consumers.consumer_category_id',
                    'swm_consumers.consumer_type_id',
                    'swm_consumers.apartment_id',
                    'swm_consumers.is_deactivate',
                    'swm_consumers.user_id',
                    'swm_consumers.user_type',
            )
            ->join('ulb_masters', 'ulb_masters.id', '=', 'swm_consumers.ulb_id')
            ->where('user_id', $userId)
            ->where('user_type', 'Citizen')
            ->get();

    }

    // 2. Get all the details of citizen.
    public function citizenAllDetails($consumerId, $userId)
    {
        return Consumer::select(
            'swm_consumers.id as consumerId',
            'swm_consumers.ward_no as WardName',
            'swm_consumers.consumer_no as ConsumerNumber',
            'swm_consumers.name as ApplicantName',
            'swm_consumers.entry_date as ApplyDate',
            'ulb_masters.ulb_name as ULBName',
            'swm_consumers.holding_no',
            'swm_consumers.mobile_no',
            'swm_consumers.address',
            'swm_consumers.pincode',
            'swm_consumers.consumer_category_id',
            'swm_consumers.consumer_type_id',
            'swm_consumers.apartment_id',
            'swm_consumers.is_deactivate',
            'swm_consumers.user_id',
            'swm_consumers.user_type',
        )
        ->join('ulb_masters', 'ulb_masters.id', '=', 'swm_consumers.ulb_id')
        ->where('swm_consumers.id', $consumerId)
        ->where('swm_consumers.user_id', $userId)
        ->get();
    }

    // 3. Get all the demand details of citizen.
    public function citizenDemandDetails($consumerId, $userId, $perPage)
    {
        return Consumer::select(
                'swm_demands.*',
            )
            ->join('ulb_masters', 'ulb_masters.id', '=', 'swm_consumers.ulb_id')
            ->join('swm_demands', 'swm_demands.consumer_id', '=', 'swm_consumers.id')
            ->where('swm_consumers.id', $consumerId)
            ->where('swm_consumers.user_id', $userId)
            ->paginate($perPage);
    }

    // 4. Get all the payment details of citizen.
    public function citizenPaymentDetails($consumerId, $userId)
    {
        return Consumer::select(
       'swm_transactions.*',
       
        )
        ->join('ulb_masters', 'ulb_masters.id', '=', 'swm_consumers.ulb_id')
        ->join('swm_transactions', 'swm_transactions.consumer_id', '=', 'swm_consumers.id')
        ->where('swm_consumers.id', $consumerId)
        ->where('swm_consumers.user_id', $userId)
        ->get();
    }

    // 5. Calculate the demand of citizen.
    public function calculateCitizenDemand($consumerId, $payUpto)
    {
        $demand = DB::table('swm_demands')
            ->where('consumer_id', $consumerId)
            ->where('paid_status', 0)
            ->where('is_deactivate', 0)
            ->whereDate('payment_to', '<=', $payUpto)
            ->orderBy('id', 'asc')
            ->sum('total_tax');

        return [
            'total_demand' => $demand,
            'payment_upto_date' => date('Y-m-t', strtotime($payUpto))
        ];
    }

    // 6. Get the order id for citizen demand.
    public function orderIdForCitizenDemand($consumerId, $payUpto)
    {
        return Consumer::select(      
        
            'swm_consumers.id as consumer_id',
            'swm_consumers.ward_no',
            'swm_consumers.consumer_no',
            'swm_consumers.name',
            'swm_consumers.mobile_no',
            'swm_consumers.address',
            'swm_consumers.ulb_id',
            DB::raw('SUM(swm_demands.total_tax) as total_demand')
        )
            ->join('swm_demands', 'swm_demands.consumer_id', '=', 'swm_consumers.id')
            ->where('swm_consumers.id', $consumerId)
            ->where('swm_demands.paid_status', 0)
            ->where('swm_demands.is_deactivate', 0)
            ->whereDate('swm_demands.payment_to', '<=', $payUpto)
            ->groupBy(
                'swm_consumers.id', 
                'swm_consumers.ward_no',
                'swm_consumers.consumer_no', 
                'swm_consumers.name',
                'swm_consumers.mobile_no', 
                'swm_consumers.address',
                'swm_consumers.ulb_id',
            )
            ->first();
    }     
    
    public function citizenPaymentBill($consumerId, $tranNo)
    {
        return Consumer::select(
            'swm_consumers.id as consumer_id',
            'swm_consumers.ward_no',
            'swm_consumers.consumer_no',
            'swm_consumers.name',
            'swm_consumers.mobile_no',
            'swm_consumers.address',
            'swm_consumers.ulb_id',
            DB::raw('SUM(swm_demands.total_tax) as total_demand')
        )
            ->join('swm_demands', 'swm_demands.consumer_id', '=', 'swm_consumers.id')
            ->where('swm_consumers.id', $consumerId)
            ->where('swm_demands.paid_status', 0)
            ->where('swm_demands.is_deactivate', 0)
            ->whereDate('swm_demands.payment_to', '<=', $tranNo)
            ->groupBy(
                'swm_consumers.id', 
                'swm_consumers.ward_no',
                'swm_consumers.consumer_no', 
                'swm_consumers.name',
                'swm_consumers.mobile_no', 
                'swm_consumers.address',
                'swm_consumers.ulb_id',
            )
            ->first();
    }
}
