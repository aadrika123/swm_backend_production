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

    public function getConsumerByNo($consumerNo)
    {
        return Consumer::where('consumer_no', $consumerNo)
            // ->where('status', 1)
            ->first();
    }

    /** 
     * | Get consumer by consumer id for citizen under care water connections 
     */
    public function getConsumerByIdsv1($consumerIds)
    {
        return Consumer::select(
            'swm_consumers.id',
            'swm_consumers.consumer_no',
            'swm_consumers.address',
            'swm_consumers.ulb_id',
            'swm_consumers.ward_no',
            'swm_consumer_categories.name as category_name',
            'swm_consumer_types.name as consumer_type_name',
            DB::raw("string_agg(DISTINCT swm_consumers.name,',') as applicant_name"),
            DB::raw("string_agg(DISTINCT swm_consumers.mobile_no::VARCHAR,',') as mobile_no"),
            DB::raw("COALESCE(SUM(swm_demands.total_tax), 0) as total_demand_amount"),
            DB::raw("min(swm_demands.payment_from) as demand_from"),
            DB::raw("max(swm_demands.payment_to) as demand_upto")
        )
            ->leftjoin('ulb_masters', 'ulb_masters.id', '=', 'swm_consumers.ulb_id')
            ->join('swm_consumer_categories', 'swm_consumer_categories.id', '=', 'swm_consumers.consumer_category_id')
            ->join('swm_consumer_types', 'swm_consumer_types.id', '=', 'swm_consumers.consumer_type_id')
            ->leftJoin('swm_demands', function ($join) {
                $join->on('swm_demands.consumer_id', '=', 'swm_consumers.id');
            })
            ->whereIn("swm_consumers.id", $consumerIds)
            ->groupBy(
                'swm_consumers.id',
                'swm_consumers.consumer_no',
                'swm_consumers.address',
                'swm_consumers.ulb_id',
                'swm_consumers.ward_no',
                'swm_consumer_categories.name',
                'swm_consumer_types.name'
            );
    }
    /** 
     * | Get consumer by consumer id for citizen under care water connections 
     */
    public function getConsumerByIdsv2($consumerIds)
    {
        return Consumer::select(
            'swm_consumers.id',
            'swm_consumers.consumer_no',
            'swm_consumers.address',
            'swm_consumers.ulb_id',
            'swm_consumers.ward_no',
            'swm_consumer_categories.name as category_name',
            'swm_consumer_types.name as consumer_type_name',
            DB::raw("string_agg(DISTINCT swm_consumers.name,',') as applicant_name"),
            DB::raw("string_agg(DISTINCT swm_consumers.mobile_no::VARCHAR,',') as mobile_no"),
            DB::raw("COALESCE(SUM(swm_demands.total_tax), 0) as total_demand_amount"),
            DB::raw("min(swm_demands.payment_from) as demand_from"),
            DB::raw("max(swm_demands.payment_to) as demand_upto")
        )
            ->leftjoin('ulb_masters', 'ulb_masters.id', '=', 'swm_consumers.ulb_id')
            ->join('swm_consumer_categories', 'swm_consumer_categories.id', '=', 'swm_consumers.consumer_category_id')
            ->join('swm_consumer_types', 'swm_consumer_types.id', '=', 'swm_consumers.consumer_type_id')
            ->leftJoin('swm_demands', function ($join) {
                $join->on('swm_demands.consumer_id', '=', 'swm_consumers.id')
                    ->where('swm_demands.paid_status', 0);
            })
            ->whereIn("swm_consumers.id", $consumerIds)
            ->groupBy(
                'swm_consumers.id',
                'swm_consumers.consumer_no',
                'swm_consumers.address',
                'swm_consumers.ulb_id',
                'swm_consumers.ward_no',
                'swm_consumer_categories.name',
                'swm_consumer_types.name'
            )
            ->havingRaw("SUM(swm_demands.total_tax) > 0");
    }
    /**
     * | Get consumer Details By ConsumerId
     * | @param conasumerId
     */
    public function getConsumerDetailById($consumerId)
    {
        return Consumer::where('id', $consumerId)
            ->firstOrFail();
    }

    /** 
     * | Get consumer by consumer id for citizen under care water connections 
     */
    public function recordDetailv1($ulbId)
    {
        return Consumer::select(
            'swm_consumers.id',
            'swm_consumers.consumer_no',
            'swm_consumers.holding_no',
            'swm_consumers.address',
            'swm_consumers.ulb_id',
            'swm_consumers.ward_no',
            'swm_consumer_categories.name as category_name',
            'swm_consumer_types.name as consumer_type_name',
            DB::raw("string_agg(DISTINCT swm_consumers.name,',') as applicant_name"),
            DB::raw("string_agg(DISTINCT swm_consumers.mobile_no::VARCHAR,',') as mobile_no"),
            DB::raw("COALESCE(SUM(swm_demands.total_tax), 0) as total_demand_amount"),
            DB::raw("min(swm_demands.payment_from) as demand_from"),
            DB::raw("max(swm_demands.payment_to) as demand_upto")
        )
            ->leftjoin('ulb_masters', 'ulb_masters.id', '=', 'swm_consumers.ulb_id')
            ->join('swm_consumer_categories', 'swm_consumer_categories.id', '=', 'swm_consumers.consumer_category_id')
            ->join('swm_consumer_types', 'swm_consumer_types.id', '=', 'swm_consumers.consumer_type_id')
            ->leftJoin('swm_demands', function ($join) {
                $join->on('swm_demands.consumer_id', '=', 'swm_consumers.id');
            })
            ->where('swm_consumers.ulb_id', $ulbId)
            ->groupBy(
                'swm_consumers.id',
                'swm_consumers.consumer_no',
                'swm_consumers.address',
                'swm_consumers.ulb_id',
                'swm_consumers.ward_no',
                'swm_consumer_categories.name',
                'swm_consumer_types.name',
                'swm_consumers.holding_no'
            );
    }
}
