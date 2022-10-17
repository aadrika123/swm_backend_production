<?php

namespace App\Repository;

use App\Models\Consumer;
use App\Models\ConsumerDeactivateDeatils;
use App\Models\Transaction;
use App\Models\Collections;
use App\Models\TransactionDeactivate;
use App\Models\TransactionModeChange;
use App\Models\TransactionVerification;
use App\Models\UserLoginDetail;
use App\Models\PaymentDeny;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\Api\Helpers;
use PhpOption\None;
use Carbon\Carbon;

/**
 * | Created On-09-24-2022 
 * | Created By-
 * | Created For- Report related api 
 */
class ReportRepository
{
    use Helpers;

    protected $dbConn;
    protected $Consumer;
    protected $ConsumerDeactivateDeatils;
    protected $Transaction;
    protected $Collections;
    protected $TransactionDetails;
    protected $TransactionDeactivate;
    protected $TransactionVerification;
    protected $PaymentDeny;
    protected $TransactionModeChange;

    public function __construct(Request $request)
    {
        $this->dbConn = $this->GetSchema($request->bearerToken());
        $this->Consumer = new Consumer($this->dbConn);
        $this->ConsumerDeactivateDeatils = new ConsumerDeactivateDeatils($this->dbConn);
        $this->Transaction = new Transaction($this->dbConn);
        $this->TransactionDeactivate = new TransactionDeactivate($this->dbConn);
        $this->TransactionVerification = new TransactionVerification($this->dbConn);
        $this->Collections = new Collections($this->dbConn);
        $this->PaymentDeny = new PaymentDeny($this->dbConn);
        $this->TransactionModeChange = new TransactionModeChange($this->dbConn);
    }

    public function ReportData(Request $request)
    {
        //echo $userId= $request->user()->id;
        // try
        // {  
        $response = array();
        if (isset($request->fromDate) && isset($request->toDate) && isset($request->reportType)) {
            $response = array();
            // if ($request->reportType == 'dailyCollection')
            //     $response = $this->DailyCollection($request->fromDate, $request->toDate, $request->wardNo, $request->consumerCategory, $request->consumerType, $request->apartmentId, $request->mode);

            //changed by talib
            if ($request->reportType == 'dailyCollection')
                $response = $this->DailyCollection($request->fromDate, $request->toDate,$request->tcId, $request->wardNo, $request->consumerCategory, $request->consumerType, $request->apartmentId, $request->mode);
            // changed by talib

            if ($request->reportType == 'conAdd')
                $response = $this->ConsumerAdd($request->fromDate, $request->toDate);

            if ($request->reportType == 'conDect')
                $response = $this->ConsumerDect($request->fromDate, $request->toDate);

            if ($request->reportType == 'tranDect')
                $response = $this->TransactionDeactivate($request->fromDate, $request->toDate, $request->tcId);

            if ($request->reportType == 'cashVeri')
                $response = $this->CashVerification($request->fromDate, $request->toDate, $request->tcId);

            if ($request->reportType == 'bankRec')
                $response = $this->BankReconcilliation($request->fromDate, $request->toDate, $request->tcId);

            if ($request->reportType == 'tcDaily')
                $response = $this->TcDailyActivity($request->fromDate, $request->toDate, $request->tcId);

            if ($request->reportType == 'tranModeChnage')
                $response = $this->TransactionModeChange($request->fromDate, $request->toDate, $request->tcId);

            return response()->json(['status' => True, 'data' => $response, 'msg' => ''], 200);
        } else {
            return response()->json(['status' => False, 'data' => $response, 'msg' => 'Undefined parameter supply'], 200);
        }
        // }
        // catch (Exception $e) 
        // {
        //     return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        // }
    }

    // public function DailyCollection($From, $Upto, $wardNo = null, $consumerCategory = null, $consumertype = null, $apartmentId = null, $mode = null)
    // {
    public function DailyCollection($From, $Upto,$tcId=null, $wardNo = null, $consumerCategory = null, $consumertype = null, $apartmentId = null, $mode = null)
    {

        $From = Carbon::create($From)->format('Y-m-d');
        $Upto = Carbon::create($Upto)->format('Y-m-d');
        $allTrans = $this->Transaction->select('tbl_transaction.*', 'tbl_consumer.ward_no', 'consumer_no', 'name', 'a.apt_code', 'a.apt_name')
            ->leftjoin('tbl_consumer', 'tbl_transaction.consumer_id', '=', 'tbl_consumer.id')
            ->leftjoin('tbl_apt_details_mstr as a', 'tbl_transaction.apt_mstr_id', '=', 'a.id')
            ->whereBetween('transaction_date', [$From, $Upto]);

        //changed by talib
        if (isset($tcId))
            $allTrans = $allTrans->where('tbl_transaction.user_id', $tcId);
        //changed by talib   
        if (isset($wardNo))
            $allTrans = $allTrans->where('tbl_consumer.ward_no', $wardNo);

        if (isset($consumerCategory))
            $allTrans = $allTrans->where('tbl_consumer.consumer_category_id', $consumerCategory);

        if (isset($consumertype))
            $allTrans = $allTrans->where('tbl_consumer.consumer_type_id', $consumertype);

        if (isset($apartmentId))
            $allTrans = $allTrans->where('tbl_consumer.apt_mstr_id', $apartmentId);

        if (isset($mode))
            $allTrans = $allTrans->where('tbl_transaction.payment_mode', $mode);

        $allTrans = $allTrans->get();

        $totCollection = 0;
        $totDemand = 0;
        $totPending = 0;
        $totCash = 0;
        $totCheque = 0;
        $totdd = 0;
        $transaction = array();
        foreach ($allTrans as $trans) {
            $collection = $this->Collections->where('transaction_id', $trans->id);
            $firstrecord = $collection->orderBy('id', 'asc')->first();
            $lastrecord = $collection->latest('id')->first();


            $getuserdata = $this->GetUserDetails($trans->user_id);
            $val['tcName'] = $getuserdata->name;
            $val['mobileNo'] = $getuserdata->contactno;
            $val['designation'] = $getuserdata->user_type;
            $val['wardNo'] = $trans->ward_no;
            $val['consumerNo'] = $trans->consumer_no;
            $val['consumerName'] = $trans->name;
            $val['apartmentCode'] = $trans->apt_code;
            $val['apartmentName'] = $trans->apt_name;
            $val['transactionNo'] = $trans->transaction_no;
            $val['transactionDate'] = Carbon::create($trans->transaction_date)->format('d-m-Y');
            $val['amount'] = $trans->total_payable_amt;
            $val['demandFrom'] = ($firstrecord) ? Carbon::create($firstrecord->payment_from)->format('d-m-Y') : '';
            $val['demandUpto'] = ($firstrecord) ? Carbon::create($lastrecord->payment_to)->format('d-m-Y') : '';
            $transaction[] = $val;

            $totCollection += $trans->total_payable_amt;
            $totDemand += $trans->total_demand_amt;
            $totPending += $trans->total_remaining_amt;


            if ($trans->payment_mode == 'Cash')
                $totCash += $trans->total_payable_amt;

            if ($trans->payment_mode == 'Cheque')
                $totCheque += $trans->total_payable_amt;

            if ($trans->payment_mode == 'DD')
                $totdd += $trans->total_payable_amt;
        }

        $response['transactions'] = $transaction;
        $response['totalCollection'] = $totCollection;
        $response['totalDemand'] = $totDemand;
        $response['totalPending'] = $totPending;
        $response['totalCash'] = $totCash;
        $response['totalCheque'] = $totCheque;
        $response['totalDD'] = $totdd;

        return $response;
    }


    public function ConsumerAdd($From, $Upto)
    {
        $response = array();
        $From = Carbon::create($From)->format('Y-m-d');
        $Upto = Carbon::create($Upto)->format('Y-m-d');


        $consumers = $this->Consumer;
        $consumers = $consumers->latest('id')
            ->where('deactivate_status', 0)
            ->whereBetween('entry_date', [$From, $Upto])
            ->paginate(1000);
        foreach ($consumers as $consumer) {
            $val['entryDate'] = Carbon::create($consumer->entry_date)->format('d-m-Y');
            $val['consumerNo'] = $consumer->consumer_no;
            $val['consumerName'] = $consumer->name;
            $val['entryBy'] = $this->GetUserDetails($consumer->user_id)->name;
            $response[] = $val;
        }
        return $response;
    }


    public function ConsumerDect($From, $Upto)
    {
        $response = array();
        $From = Carbon::create($From)->format('Y-m-d');
        $Upto = Carbon::create($Upto)->format('Y-m-d');


        $consumers = $this->ConsumerDeactivateDeatils->latest('id')
            ->select('tbl_consumer_deactivate_detail.*', 'name', 'consumer_no')
            ->join('tbl_consumer', 'tbl_consumer_deactivate_detail.consumer_id', '=', 'tbl_consumer.id')
            ->whereBetween('entry_date', [$From, $Upto])
            ->paginate(1000);

        foreach ($consumers as $consumer) {
            $val['deactivateDate'] = Carbon::create($consumer->deactivation_date)->format('d-m-Y');
            $val['consumerNo'] = $consumer->consumer_no;
            $val['consumerName'] = $consumer->name;
            $val['deactivateBy'] = $this->GetUserDetails($consumer->deactivated_by)->name;
            $val['remarks'] = $consumer->remarks;
            $response[] = $val;
        }
        return $response;
    }


    public function TransactionDeactivate($From, $Upto, $tcId = null)
    {
        $response = array();
        $From = Carbon::create($From)->format('Y-m-d');
        $Upto = Carbon::create($Upto)->format('Y-m-d');


        $transaction = $this->TransactionDeactivate->latest('id')
            ->select('tbl_transaction_deactivate.*', 'transaction_date', 'total_payable_amt', 'tbl_transaction.user_id as transby', 'name', 'consumer_no', 'tbl_transaction.payment_mode', 'a.apt_code', 'a.apt_name')
            ->join('tbl_transaction', 'tbl_transaction_deactivate.transaction_id', '=', 'tbl_transaction.id')
            ->leftjoin('tbl_consumer', 'tbl_transaction.consumer_id', '=', 'tbl_consumer.id')
            ->leftjoin('tbl_apt_details_mstr as a', 'tbl_transaction.apt_mstr_id', '=', 'a.id');
        if (isset($tcId))
            $transaction = $transaction->where('tbl_transaction.user_id', $tcId);
        $transaction = $transaction->whereBetween('date', [$From, $Upto])
            ->paginate(1000);

        foreach ($transaction as $trans) {
            $val['deactivateDate'] = Carbon::create($trans->date)->format('d-m-Y');
            $val['transactionDate'] = Carbon::create($trans->transaction_date)->format('d-m-Y');
            $val['amount'] = $trans->total_payable_amt;
            $val['transactionBy'] = $this->GetUserDetails($trans->transby)->name;
            $val['consumerName'] = $trans->name;
            $val['consumerNo'] = $trans->consumer_no;
            $val['apartmentName'] = $trans->apt_name;
            $val['apartmentCode'] = $trans->apt_code;
            $val['transactionMode'] = $trans->payment_mode;
            $val['deactivateBy'] = $this->GetUserDetails($trans->user_id)->name;
            $val['remarks'] = $trans->remarks;
            $response[] = $val;
        }
        return $response;
    }


    public function CashVerification($From, $Upto, $tcId = null)
    {
        $response = array();
        $From = Carbon::create($From)->format('Y-m-d');
        $Upto = Carbon::create($Upto)->format('Y-m-d');


        $transaction = $this->TransactionVerification->latest('id')
            ->select('tbl_transaction_verification.*', 'transaction_date', 'total_payable_amt', 'tbl_transaction.user_id as transby', 'name', 'consumer_no', 'tbl_transaction.payment_mode', 'a.apt_code', 'a.apt_name')
            ->join('tbl_transaction', 'tbl_transaction_verification.transaction_id', '=', 'tbl_transaction.id')
            ->leftjoin('tbl_consumer', 'tbl_transaction.consumer_id', '=', 'tbl_consumer.id')
            ->leftjoin('tbl_apt_details_mstr as a', 'tbl_transaction.apt_mstr_id', '=', 'a.id');
        if (isset($tcId))
            $transaction = $transaction->where('tbl_transaction.user_id', $tcId);
        $transaction = $transaction->whereBetween('verify_date', [$From, $Upto])
            ->paginate(1000);

        foreach ($transaction as $trans) {
            $val['verifiedDate'] = Carbon::create($trans->verify_date)->format('d-m-Y');
            $val['transactionDate'] = Carbon::create($trans->transaction_date)->format('d-m-Y');
            $val['amount'] = $trans->amount;
            $val['transactionBy'] = $this->GetUserDetails($trans->transby)->name;
            $val['consumerName'] = $trans->name;
            $val['consumerNo'] = $trans->consumer_no;
            $val['apartmentName'] = $trans->apt_name;
            $val['apartmentCode'] = $trans->apt_code;
            $val['transactionMode'] = $trans->payment_mode;
            $val['verifiedBy'] = $this->GetUserDetails($trans->verify_by)->name;
            $val['remarks'] = $trans->remarks;
            $response[] = $val;
        }
        return $response;
    }

    public function BankReconcilliation($From, $Upto, $tcId = null)
    {
        $response = array();
        $From = Carbon::create($From)->format('Y-m-d');
        $Upto = Carbon::create($Upto)->format('Y-m-d');

        $sql = "SELECT bank_cancel_id,reconcilition_date,t.transaction_no,transaction_date,t.payment_mode,cheque_no, cheque_date, bank_name,branch_name, total_payable_amt,bc.remarks,t.user_id as transby, name, consumer_no, a.apt_code, a.apt_name, bc.user_id as verify_by
            FROM  tbl_transaction t
            JOIN tbl_bank_cancel bc on bc.transaction_id=t.id
            LEFT JOIN tbl_consumer c on t.consumer_id=c.id
            LEFT JOIN tbl_apt_details_mstr a on t.apt_mstr_id=a.id
            LEFT JOIN tbl_bank_cancel_details bd on bd.bank_cancel_id=bc.id
            LEFT JOIN tbl_transaction_details td on td.transaction_id=t.id
            WHERE (transaction_date BETWEEN '$From' and '$Upto') and t.pad_status>0 ";

        $transactions = DB::connection($this->dbConn)->select($sql);

        foreach ($transactions as $trans) {
            $val['clearanceDate'] = ($trans->reconcilition_date) ? Carbon::create($trans->reconcilition_date)->format('d-m-Y') : '';
            $val['amount'] = $trans->total_payable_amt;
            $val['transactionDate'] = Carbon::create($trans->transaction_date)->format('d-m-Y');
            $val['transactionBy'] = $this->GetUserDetails($trans->transby)->name;
            $val['consumerName'] = $trans->name;
            $val['consumerNo'] = $trans->consumer_no;
            $val['apartmentName'] = $trans->apt_name;
            $val['apartmentCode'] = $trans->apt_code;
            $val['transactionMode'] = $trans->payment_mode;
            $val['chequeNo'] = $trans->cheque_no;
            $val['chequeDate'] = ($trans->cheque_date) ? Carbon::create($trans->cheque_date)->format('d-m-Y') : '';
            $val['bankName'] = $trans->bank_name;
            $val['branchName'] = $trans->branch_name;
            $val['verifiedBy'] = $this->GetUserDetails($trans->verify_by)->name;
            $val['remarks'] = $trans->remarks;
            $response[] = $val;
        }
        return $response;
    }


    public function TcDailyActivity($From, $Upto, $tcId)
    {
        $response = array();
        $From = Carbon::create($From);
        $Upto = Carbon::create($Upto);

        $tc_details = $this->GetUserDetails($tcId);
        $response['tcName'] = $tc_details->name;
        $response['mobileNo'] = $tc_details->contactno;
        $response['userType'] = $tc_details->user_type;

        $maindata = array();


        for ($i = $From; $i <= $Upto; $i->modify('+1 day')) {
            $loginarr = array();
            $transarr = array();
            $denayarr = array();
            $denayamountarr = array();
            $collectionarr = array();
            $date = $i->format("Y-m-d");
            $val['date'] = $date;

            $user_login = UserLoginDetail::where('user_id', $tcId)
                ->whereDate('timestamp', $date)
                ->get();

            foreach ($user_login as $log) {
                $loginarr[] = $log->login_time;
            }

            $consumer_count = $this->Consumer->where('user_id', $tcId)
                ->whereDate('entry_date', $date)
                ->count();


            $trans = $this->Transaction->where('user_id', $tcId)
                ->whereDate('transaction_date', $date)
                ->get();
            foreach ($trans as $t) {
                $collectionarr[] = $t->total_payable_amt;
                $transarr[] = Carbon::create($t->stamp_date)->format('h:i:s a');
            }

            $deny = $this->PaymentDeny->where('user_id', $tcId)
                ->whereDate('deny_date', $date)
                ->get();

            foreach ($deny as $d) {
                $denayamountarr[] = $d->outstanding_amt;
                $denayarr[] = Carbon::create($d->deny_date)->format('h:i:s a');
            }


            $val['loginTime'] = $loginarr;
            $val['addedConsumerQuantity'] = $consumer_count;
            $val['collectionTime'] = $transarr;
            $val['collectionAmount'] = $collectionarr;
            $val['paymentDeniedTime'] = $denayarr;
            $val['paymentDeniedAmount'] = $denayamountarr;
            $maindata[] = $val;
        }
        $response['data'] = $maindata;

        return $response;
    }



    public function TransactionModeChange($From, $Upto, $tcId = null)
    {
        $response = array();
        $From = Carbon::create($From);
        $Upto = Carbon::create($Upto);

        $mchange = $this->TransactionModeChange->select('tbl_transaction_mode_change.*', 'transaction_date', 'total_payable_amt as amount', 't.user_id as transby', 'c.name as consumer_name', 'c.consumer_no', 'a.apt_name', 'a.apt_code')
            ->join('tbl_transaction as t', 'tbl_transaction_mode_change.transaction_id', '=', 't.id')
            ->leftjoin('tbl_consumer as c', 't.consumer_id', '=', 'c.id')
            ->leftjoin('tbl_apt_details_mstr as a', 't.apt_mstr_id', '=', 'a.id')
            ->whereBetween('tbl_transaction_mode_change.date', [$From, $Upto])
            ->where('tbl_transaction_mode_change.status', 1);
        if (isset($tcId))
            $mchange = $mchange->where('tbl_transaction_mode_change.user_id', $tcId);
        $mchange = $mchange->latest('tbl_transaction_mode_change.id')->get();

        foreach ($mchange as $trans) {
            $val['changeDate'] = Carbon::create($trans->date)->format('d-m-Y');
            $val['transactionDate'] = Carbon::create($trans->transaction_date)->format('d-m-Y');
            $val['amount'] = $trans->amount;
            $val['transactionBy'] = $this->GetUserDetails($trans->transby)->name;
            $val['consumerName'] = $trans->consumer_name;
            $val['consumerNo'] = $trans->consumer_no;
            $val['apartmentName'] = $trans->apt_name;
            $val['apartmentCode'] = $trans->apt_code;
            $val['oldTransactionMode'] = $trans->previous_mode;
            $val['newTransactionMode'] = $trans->current_mode;
            $val['changeBy'] = $this->GetUserDetails($trans->user_id)->name;
            $response[] = $val;
        }

        return $response;
    }
}
