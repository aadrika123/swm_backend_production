<?php

namespace App\Repository;

use App\Models\Consumer;
use App\Models\Demand;
use App\Models\Apartment;
use App\Models\ConsumerType;
use App\Models\ConsumerCategory;
use App\Models\ConsumerDeactivateDeatils;
use App\Models\Transaction;
use App\Models\TransactionDetails;
use App\Models\TransactionDeactivate;
use App\Models\GeoLocation;
use App\Models\Collections;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


/**
 * | Created On-08-09-2022 
 * | Created By-
 * | Created For- Consumer related api 
 */
class ConsumerRepository
{
    private $schema = 'db_ranchi';

    
    public function ConsumerList(Request $request)
    {
        //echo $userId= $request->user()->id;
        try
        {   
            
            $conArr = array();
            if(isset($request->id) || isset($request->consumerNo) || isset($request->consumerName) || isset($request->mobileNo))
            {
                if(isset($request->id))
                {
                    $field = 'tbl_consumer.id';
                    $operator = '=';
                    $value = $request->id;
                }

                if(isset($request->consumerNo))
                {
                    $field = 'consumer_no';
                    $operator = '=';
                    $value = $request->consumerNo;
                }

                if(isset($request->consumerName))
                {
                    $field = 'tbl_consumer.name';
                    $operator = 'like';
                    $value = '%'.$request->consumerName.'%';
                }

                if(isset($request->mobileNo))
                {
                    $field = 'mobile_no';
                    $operator = '=';
                    $value = $request->mobileNo;
                }
                
                $consumerList = Consumer::join('tbl_consumer_category', 'tbl_consumer.consumer_category_id', '=', 'tbl_consumer_category.id')
                                ->join('tbl_consumer_type', 'tbl_consumer.consumer_type_id', '=', 'tbl_consumer_type.id')
                                ->select(DB::raw('tbl_consumer.*, tbl_consumer_category.name as category, tbl_consumer_type.name as type'))
                                ->where($field, $operator, $value)
                                ->get();
                

                foreach($consumerList as $consumer)
                {
                    $demand = Demand::where('consumer_id', $consumer->id)
                                ->where('paid_status', 0)
                                ->where('deactivate_status', 0)
                                ->orderBy('id', 'asc')
                                ->get();
                    $total_tax = 0.00;
                    $demand_upto = '';
                    $paid_status = 'True';
                    foreach($demand as $dmd)
                    {
                        $total_tax += $dmd->total_tax;
                        $demand_upto = $dmd->demand_date;
                        $paid_status = 'False';
                    }
                    // $demand = Demand::select(DB::raw('consumer_id,sum(total_tax) as total_tax,paid_status,deactivate_status,max(demand_date) as demand_upto'))
                    //             ->where('consumer_id', $consumer->id)
                    //             ->where('paid_status', 0)
                    //             ->where('deactivate_status', 0)
                    //             ->groupByRaw('consumer_id,paid_status,deactivate_status')
                    //             ->first();
                            
                    // $category = DB::table('tbl_consumer_category')
                    //                 ->where('id', $consumer->consumer_category_id)->first();
                    
                    $con['id'] = $consumer->id;
                    $con['wardNo'] = $consumer->ward_no;
                    $con['holdingNo'] = $consumer->holding_no;
                    $con['consumerName'] = $consumer->name;
                    $con['apartmentId'] = $consumer->apt_mstr_id;
                    $con['consumerNo'] = $consumer->consumer_no;
                    $con['Address'] = $consumer->address;
                    $con['ps'] = $consumer->police_station;
                    $con['landmark'] = $consumer->landmark;
                    $con['houseNo'] = $consumer->house_no;
                    $con['pinCode'] = $consumer->pincode;
                    $con['locality'] = $consumer->locality;
                    $con['cansumerCategory'] = $consumer->category;
                    $con['cansumerType'] = $consumer->type;
                    $con['mobileNo'] = $consumer->mobile_no;
                    $con['activeDemandDetails'] = $demand;
                    $con['totalDemand'] = $total_tax;
                    $con['demandUpto'] = $demand_upto;
                    $con['paidStatus'] = $paid_status;
                    $con['applyBy'] = $consumer->user_id;
                    $con['applyDate'] = date("d-m-Y", strtotime($consumer->entry_date));

                    $conArr[] = $con;

                }
                return response()->json(['status'=> True, 'data'=>$conArr, 'msg'=> ''], 200);
            }else{
                return response()->json(['status'=> False, 'data'=>$conArr, 'msg'=> 'Undefined parameter supply'], 200);
            }
            
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function ApartmentList(Request $request)
    {

        try
        {   

            $conArr = array();

            if(isset($request->apartmentId) || isset($request->apartmentName))
            {
                if(isset($request->apartmentId))
                {
                    $field = 'id';
                    $operator = '=';
                    $value = $request->apartmentId;
                }

                if(isset($request->apartmentName))
                {
                    $field = 'apt_name';
                    $operator = '=';
                    $value = $request->apartmentName;
                }
                
                $apartmentList = Apartment::where($field, $operator, $value)
                                    ->get();
                
                foreach($apartmentList as $apartment)
                {

                    $demand = DB::connection($this->schema)->table('tbl_demand')
                                ->select(DB::raw('consumer_id,sum(total_tax) as total_tax,paid_status,tbl_demand.deactivate_status,max(demand_date) as demand_upto'))
                                ->join('tbl_consumer', 'tbl_demand.id', '=', 'tbl_demand.consumer_id')
                                ->where('tbl_consumer.apt_mstr_id', $apartment->id)
                                ->where('paid_status', 0)
                                ->where('tbl_demand.deactivate_status', 0)
                                ->groupByRaw('consumer_id,paid_status,deactivate_status')
                                ->first();
                    
                    $con['id'] = $apartment->id;
                    $con['wardNo'] = $apartment->ward_no;
                    $con['apartmentName'] = $apartment->apt_name;
                    $con['apartmentCode'] = $apartment->apt_code;
                    $con['address'] = $apartment->apt_address;
                    $con['mobileNo'] = $apartment->mobile_no;
                    $con['totalDemand'] = ($demand)?$demand->total_tax:'0.00';
                    $con['demandUpto'] = ($demand)?$demand->demand_upto:'';
                    $con['paidStatus'] = ($demand)?$demand->paid_status:'';


                    $conArr[] = $con;

                }
                return response()->json(['status'=> True, 'data'=>$conArr, 'msg'=> ''], 200);
            }else{
                return response()->json(['status'=> False, 'data'=>$conArr, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function getApartmentDetails(Request $request)
    {

        try
        {   

            $conArr = array();
            if(isset($request->id))
            {
            
                $apartment = Apartment::leftJoin('tbl_consumer as c', 'tbl_apt_details_mstr.id', '=', 'c.apt_mstr_id')
                                    ->select(DB::raw('tbl_apt_details_mstr.*, c.id as consumer_id, c.police_station, c.landmark, c.landmark, c.house_no, c.pincode, c.locality'))
                                    ->where('tbl_apt_details_mstr.id', $request->id)
                                    ->first();
                

                $demand = Demand::where('consumer_id', $apartment->consumer_id)
                            ->where('paid_status', 0)
                            ->where('deactivate_status', 0)
                            ->get();
                
                $con['id'] = $apartment->id;
                $con['wardNo'] = $apartment->ward_no;
                $con['apartmentName'] = $apartment->apt_name;
                $con['apartmentCode'] = $apartment->apt_code;
                $con['address'] = $apartment->apt_address;
                $con['mobileNo'] = $apartment->mobile_no;
                $con['ps'] = $apartment->police_station;
                $con['landmark'] = $apartment->landmark;
                $con['houseNo'] = $apartment->house_no;
                $con['pinCode'] = $apartment->pincode;
                $con['locality'] = $apartment->locality;
                $con['activeDemandDetails'] = $demand;
                $con['applyBy'] = $apartment->user_id;
                $con['applyDate'] = date("d-m-Y", strtotime($apartment->entry_date));


                $conArr[] = $con;
                return response()->json(['status'=> True, 'data'=>$conArr, 'msg'=> ''], 200);
            }
            else{
                return response()->json(['status'=> False, 'data'=>$conArr, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function ConsumerAdd(Request $request)
    {
        
        try {

            $validator = Validator::make($request->all(), [
                'consumerName' => 'required',
                'wardNo' => 'required',
                'holdingNo' => 'required',
                'mobileNo' => 'required',
                'ps' => 'required',
                'landmark' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'pinCode' => 'required',
                'consumerCategory' => 'required',
                'consumerType' => 'required',
                'demandFrom' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }

            $apartId = Null;
            $apartCode = '';
            $apartCount = 0;
            $consumerNo = '';
            $apartName = '';
            $userId = 1;
            
            if(isset($request->apartmentId))
            {
                $apart = Apartment::select('apt_code', 'apt_name')->where('id', $request->apartmentId)->first();
                $getConsum = Consumer::select('consumer_no')->where('apt_mstr_id', $request->apartmentId)->first();
                $apartId = $request->apartmentId;
                $apartCode = $apart->apt_code;
                $apartName = $apart->apt_name;
                if($getConsum->count()> 0)
                    $apartCount = $getConsum->count()+1;
                $consumerNo = substr($getConsum->consumer_no, 0, 10).str_pad($apartCount, 4, "0", STR_PAD_LEFT);
            }
           
            $consumer = new Consumer();
            $consumer->setConnection($this->schema);
            $consumer->ward_no = $request->wardNo;
            $consumer->apt_mstr_id = $apartId;
            $consumer->apt_code = $apartCode;
            $consumer->holding_no = $request->holdingNo;
            $consumer->name = $request->consumerName;
            $consumer->mobile_no = $request->mobileNo;
            $consumer->owner_id = 0;
            $consumer->police_station = $request->ps;
            $consumer->landmark = $request->landmark;
            $consumer->house_no = $request->houseNo;
            $consumer->address = $request->address;
            $consumer->locality = $request->locality;
            $consumer->pincode = $request->pinCode;
            $consumer->consumer_category_id = $request->consumerCategory;
            $consumer->consumer_type_id = $request->consumerType;
            $consumer->user_id = $userId;
            $consumer->entry_date = date('Y-m-d');
            $consumer->creation_date = date('Y-m-d');
            $consumer->created_by = $userId;
            $consumer->date_time = date('Y-m-d H:i:s');
            $consumer->deactivate_status = 0;
            $consumer->save();

            $consumerUpdate = Consumer::find($consumer->id);
            
            if((!isset($request->apartmentId) || empty($request->apartmentId)) || $apartCount == 1)
            {
                $serialNo='0001';
                $wardCreated= str_pad($request->wardNo, 2, "0", STR_PAD_LEFT);
                $consumerTypeCreated= str_pad($request->consumerType, 2, "0", STR_PAD_LEFT);
                $randCreated= str_pad($consumer->id, 5, "0", STR_PAD_LEFT);
                
                $consumerNo = $wardCreated.$request->consumerCategory.$consumerTypeCreated.$randCreated.$serialNo;
            }
            $consumerUpdate->consumer_no = $consumerNo ;
            $consumerUpdate->entry_type = 1;
            $consumerUpdate->save();

            $consumerType = consumerType::select('rate')
                            ->where('id', $request->consumerType)
                            ->first();

            $taxRate = $consumerType->rate;
            $demandFrom = strtotime(date('Y-m-d', strtotime($request->demandFrom)));
            $demandUpto = strtotime(date('Y-m-d'));
            $demand = array();
            while ($demandFrom <= $demandUpto)
            {
                
                $payment_from=date('Y-m-d', $demandFrom);
                $payment_to=date('Y-m-t', strtotime($payment_from));
                $demandFrom = strtotime('+1 month', $demandFrom);
                $dmd = new Demand();
                $dmd->setConnection($this->schema);
                $dmd->consumer_id = $consumer->id;
                $dmd->total_tax = $taxRate;
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
            $response['wardNo'] = $request->wardNo;
            $response['apartmentId'] = $request->apartmentId;
            $response['holdingNo'] = $request->holdingNo;
            $response['consumerNo'] = $consumerNo;
            $response['consumerName'] = $request->consumerName;
            $response['apartmentName'] = $apartName;
            $response['mobileNo'] = $request->mobileNo;
            $response['ps'] = $request->ps;
            $response['landmark'] = $request->landmark;
            $response['houseNo'] = $request->houseNo;
            $response['address'] = $request->address;
            $response['locality'] = $request->locality;
            $response['pinCode'] = $request->pinCode;
            $response['consumerCategory'] = consumerCategory::select('name')->first()->name;
            $response['consumerType'] = $consumerType->name;
            $response['demandFrom'] = $request->demandFrom;
            $response['demandDetails'] = $demand;
            $response['appliedBy'] = $userId;
            $response['appliedDate'] = date('Y-m-d');

            return response()->json(['status' => true, 'data' => $response, 'msg' => "Consumer created and demand generated successfully"], 200);
        } catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
    }


    public function RenterFormData(Request $request)
    {

        try
        {   

            $response = array();

            if(isset($request->consumerId))
            {
                $getconsumer = Consumer::where('id', $request->consumerId)
                                    ->first();

                $response['wardNo'] = $getconsumer->ward_no;
                $response['ownerName'] = $getconsumer->name;
                $response['holdingNo'] = $getconsumer->holding_no;
                $response['consumerCategoryList'] = ConsumerCategory::get();


                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function EditConsumerDetailsById(Request $request)
    {
        
        try
        {   
            $response = array();
            if(isset($request->id))
            {
                
                $response = (array)$this->ConsumerList($request)->original['data'][0];
                
                if(count($response)> 0 && !empty($response['apartmentId']))
                {
                    $apart = Apartment::select('apt_code', 'apt_name')
                                ->where('id', $response['apartmentId'])
                                ->first();
                    
                    $response['apartmentName'] = $apart->apt_name;
                    $response['apartmentCode'] = $apart->apt_code;
                }
                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }



    public function DeactivateConsumer(Request $request)
    {
        $userId = 1;
        try
        {   
            $response = array();
            if(isset($request->consumerId))
            {
                
                
                $consDtls = new ConsumerDeactivateDeatils();
                $consDtls->setConnection($this->schema);
                $consDtls->consumer_id = $request->consumerId;
                $consDtls->remarks = ($request->remarks)?$request->remarks:"";
                $consDtls->deactivated_by = $userId;
                $consDtls->deactivation_date = date('Y-m-d');
                $consDtls->ip_address = $request->ip();
                $consDtls->timestamp = date('Y-m-d H:i:s');
                $consDtls->save();
                
                if($consDtls->id > 0)
                {
                    $consumer = Consumer::find($request->consumerId);
                    $consumer->deactivate_status = 1;
                    $consumer->save();

                    Demand::where('consumer_id', $request->consumerId)
                            ->where('paid_status', 0)
                            ->update('deactivate_status', 1);

                    return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Deactivated Successfully'], 200);
                }else{
                    return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Deactivate issue, please check'], 200);
                }
                
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function PaymentData(Request $request)
    {
        try
        {   
            $response = array();
            if(isset($request->consumerId))
            {
                $demand = Demand::where('consumer_id', $request->consumerId)
                                ->where('paid_status', 0)
                                ->where('deactivate_status', 0)
                                ->orderby('id', 'asc')
                                ->get();
                
                $totalDmd = 0;
                $paymentUptoMonth = '';
                foreach($demand as $dmd)
                {
                    $totalDmd += $dmd->total_tax;
                    $paymentUptoMonth = date('M', strtotime($dmd->payment_to));
                }
                $response['demand'] = $demand;
                $response['totaldemand'] = $totalDmd;
                $response['paymentUptoMonth'] = $paymentUptoMonth;
                

                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
                
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function CalculatedAmount(Request $request)
    {
        try
        {   
            $response = array();
            if(isset($request->consumerId))
            {
                $demand = Demand::where('consumer_id', $request->consumerId)
                                ->where('paid_status', 0)
                                ->where('deactivate_status', 0)
                                ->orderby('id', 'asc')
                                ->get();
                
                $totalDmd = 0;
                $paymentUptoDate = date('Y-m-t', strtotime(date('Y').$request->payUptoMonth.'01'));
                // $pay
                foreach($demand as $dmd)
                {
                    if(strtotime($dmd->payment_to) <= strtotime($paymentUptoDate))
                    {
                        $totalDmd += $dmd->total_tax;
                    }
                    
                }
                $response['totaldemand'] = $totalDmd;
                $response['paymentUptoDate'] = $paymentUptoDate;
                

                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
                
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function DashboardData(Request $request)
    {
        try
        {   
            $response = array();

            $Consumer = Consumer::query();
            $totalDmd = Demand::query()->where('paid_status', 0)
                                ->where('deactivate_status', 0);
            $Collection = Transaction::query()->select('pad_status', 'transaction_date', 'total_payable_amt')->leftjoin('tbl_transaction_deactivate', 'tbl_transaction_deactivate.transaction_id', '=', 'tbl_transaction.id')
                                            ->whereNull('transaction_id');
            
            if(isset($request->month) && isset($request->year))
            {
                $From = $request->year.'-'.$request->month.'-01';
                $Upto = date('Y-m-t', strtotime($From));
                $Consumer = $Consumer->whereBetween('entry_date', [$From, $Upto]);
                $totalDmd = $totalDmd->whereBetween('demand_date', [$From, $Upto]);
                $Collection = $Collection->whereBetween('transaction_date', [$From, $Upto]);
            }

            $Consumer = $Consumer->get();
            $TotalConsumer = $Consumer->count();
            $totalDmd = $totalDmd->sum('total_tax');

            $Collection = $Collection->get();
            $totalCollection = 0;
            $pendingCollection = 0;
            $totalResidential = 0;
            $totalcom1 = 0;
            $totalcom2 = 0;
            
            foreach($Collection as $coll)
                if($coll->pad_status != 0 )
                {
                    $totalCollection += $coll->total_payable_amt;
                }else{
                    $pendingCollection += $coll->total_payable_amt;
                }

            foreach($Consumer as $con)
            {
                if($con->consumer_category_id == 1)
                    $totalResidential += 1;
                if($con->consumer_category_id == 2)
                    $totalcom1 += 1;
                if($con->consumer_category_id == 3)
                    $totalcom2 += 1;
            }


            $response['totalDemand'] = $totalDmd;
            $response['totalConsumer'] = $TotalConsumer;
            $response['totalCollection'] = $totalCollection;
            $response['pendingCollection'] = $pendingCollection;
            $response['totalResidenstialConsumer'] = $totalResidential;
            $response['totalCommercial1Consumer'] = $totalcom1;
            $response['totalCommercial2Consumer'] = $totalcom2;
            

            return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
                
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function GetTrancation(Request $request)
    {
        try
        {   
            $response = array();
            if(isset($request->transactionNo))
            {
                $sql = "SELECT t.*,c.name as consumer_name,consumer_no,a.apt_code,a.apt_name,u.name as transaction_by,
                c.ward_no, c.holding_no, c.address,c.apt_mstr_id,u.contactno
                FROM tbl_transaction t
                LEFT JOIN tbl_consumer c on t.consumer_id=c.id
                LEFT JOIN tbl_apt_details_mstr a on t.apt_mstr_id=a.id
                JOIN db_master.view_user_mstr u on t.user_id=u.id
                WHERE t.transaction_no ='".$request->transactionNo."'";
                
                
                $tran = DB::connection($this->schema)->select($sql);

                if($tran)
                {

                    $tran = $tran[0];

                    $response['transactionNo'] = $tran->transaction_no;
                    $response['transactionDate'] = date('d-m-Y', strtotime($tran->transaction_date));
                    $response['transactionAmount'] = $tran->total_payable_amt;
                    $response['transactionBy'] = $tran->transaction_by;
                    $response['consumerNo'] = $tran->consumer_no;
                    $response['consumerName'] = $tran->consumer_name;
                    $response['apartmentCode'] = $tran->apt_code;
                    $response['apartmentName'] = $tran->apt_name;
                    $response['wardNo'] = $tran->ward_no;
                    $response['holdingNo'] = $tran->holding_no;
                    $response['address'] = $tran->address;
                    $response['totalDemand'] = $tran->total_demand_amt;
                    $response['remainingAmount'] = $tran->total_remaining_amt;
                    $response['paidStatus'] = ($tran->pad_status == 1)? "Paid":"Pending";
                    $response['paymentMode'] = $tran->payment_mode;
                    $response['tcName'] = $tran->transaction_by;
                    $response['tcMobileNo'] = $tran->contactno;
                }

                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
                
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function TransactionDeactivate(Request $request)
    {
        
        try
        {   
            
            $userId = 1;
            $status = '';
            $data = '';
            $msg = '';
            
            $validator = Validator::make($request->all(), [
                'transactionNo' => 'required',
                'receiptFile' => 'required|mimes:jpeg,png,jpg,png,pdf|max:1024',
                'remarks' => 'required'
            ]);
            
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            
            if(isset($request->transactionNo))
            {
                
                $tran = Transaction::select('id', 'pad_status', 'payment_mode')
                                    ->where('transaction_no', $request->transactionNo)
                                    ->where('pad_status', '!=', 0)
                                    ->first();
                
                if($tran)
                {
                    if($tran->payment_mode != "Cash" && $tran->pad_status != 2)
                    {
                        $status = True;
                        $data = '';
                        $msg = "Transaction Cant be deactivate because of Tranction No.".$request->transactionNo. "Cleared fom bank end.";
                        return response()->json(['status'=> True,  'msg'=> $msg], 200);
                    }
                    else
                    {
                        $filePath = '';
                        if(!empty($request->receiptFile))
                        {
                            $filePath = md5($request->transactionNo).'.'.$request->receiptFile->extension();
                            $request->receiptFile->move(public_path('uploads'), $filePath);
                        }

                        $transDeactivate = new TransactionDeactivate();
                        $transDeactivate->setConnection($this->schema);
                        $transDeactivate->transaction_id = $tran->id;
                        $transDeactivate->date = date('Y-m-d');
                        $transDeactivate->remarks = ($request->remarks)?$request->remarks:"";
                        $transDeactivate->img_path = $filePath;
                        $transDeactivate->stampdate = date('Y-m-d H:i:s');
                        $transDeactivate->user_id = $userId;
                        $transDeactivate->ip_address = $request->ip();
                        $transDeactivate->save();
                        
                        if($transDeactivate->id > 0)
                        {
                            $tran->pad_status = 0;
                            $tran->save();
                        }
                        
                        $status = True;
                        $msg = "Deactivated Successfully";
                    }
                }
                else
                {
                    $status = False;
                    $msg = "Transaction No. not found";
                }
                
            }else{
                $status = False;
                $msg = "Undefined parameter supply";
            }
            return response()->json(['status'=> $status, 'data'=>$data, 'msg'=> $msg], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function AddRenter(Request $request)
    {
        
        try {

            $validator = Validator::make($request->all(), [
                'consumerId' => 'required',
                'consumerName' => 'required',
                'wardNo' => 'required',
                'holdingNo' => 'required',
                'mobileNo' => 'required',
                'ps' => 'required',
                'landmark' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'pinCode' => 'required',
                'consumerCategory' => 'required',
                'consumerType' => 'required',
                'demandFrom' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }

            $apartId = Null;
            $apartName = '';
            $apartCode = '';
            $userId = 1;
            $response = array();

            $getConsumer = Consumer::where('name', $request->consumerName)
                                    ->where('mobile_no', $request->mobileNo)
                                    ->where('consumer_category_id', $request->consumerCategory)
                                    ->where('consumer_type_id', $request->consumerType)
                                    ->where('ward_no', $request->wardNo)
                                    ->where('deactivate_status', 0)
                                    ->get();
            
            if($getConsumer->count() == 0)
            {

                if(isset($request->apartmentId))
                {
                    $apart = Apartment::select('apt_code', 'apt_name')->where('id', $request->apartmentId)->first();
                    $apartId = $request->apartmentId;
                    $apartCode = $apart->apt_code;
                    $apartName = $apart->apt_name;
                }

            
                $consumer = new Consumer();
                $consumer->setConnection($this->schema);
                $consumer->ward_no = $request->wardNo;
                $consumer->apt_mstr_id = $apartId;
                $consumer->apt_code = $apartCode;
                $consumer->holding_no = $request->holdingNo;
                $consumer->name = $request->consumerName;
                $consumer->mobile_no = $request->mobileNo;
                $consumer->owner_id = $request->consumerId;
                $consumer->police_station = $request->ps;
                $consumer->landmark = $request->landmark;
                $consumer->house_no = $request->houseNo;
                $consumer->address = $request->address;
                $consumer->locality = $request->locality;
                $consumer->pincode = $request->pinCode;
                $consumer->consumer_category_id = $request->consumerCategory;
                $consumer->consumer_type_id = $request->consumerType;
                $consumer->user_id = $userId;
                $consumer->entry_date = date('Y-m-d');
                $consumer->creation_date = date('Y-m-d');
                $consumer->created_by = $userId;
                $consumer->date_time = date('Y-m-d H:i:s');
                $consumer->deactivate_status = 0;
                $consumer->save();
            
                $consumerUpdate = Consumer::find($consumer->id);

                $serialNo='0001';
                $wardCreated= str_pad($request->wardNo, 2, "0", STR_PAD_LEFT);
                $consumerTypeCreated= str_pad($request->consumerType, 2, "0", STR_PAD_LEFT);
                $randCreated= str_pad($consumer->id, 5, "0", STR_PAD_LEFT);
                
                $consumerUpdate->consumer_no = $wardCreated.$request->consumerCategory.$consumerTypeCreated.$randCreated.$serialNo;

                $consumerUpdate->entry_type = 1;
                $consumerUpdate->save();

                $consumerType = consumerType::select('rate', 'name')
                                ->where('id', $request->consumerType)
                                ->first();

                $taxRate = $consumerType->rate;
                $demandFrom = strtotime(date('Y-m-d', strtotime($request->demandFrom)));
                $demandUpto = strtotime(date('Y-m-d'));
                $demand = array();
                while ($demandFrom <= $demandUpto)
                {
                    
                    $payment_from=date('Y-m-d', $demandFrom);
                    $payment_to=date('Y-m-t', strtotime($payment_from));
                    $demandFrom = strtotime('+1 month', $demandFrom);
                    
                    $dmd = new Demand();
                    $dmd->setConnection($this->schema);
                    $dmd->consumer_id = $consumer->id;
                    $dmd->total_tax = $taxRate;
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

                $response['wardNo'] = $request->wardNo;
                $response['apartmentId'] = $request->apartmentId;
                $response['holdingNo'] = $request->holdingNo;
                $response['consumerName'] = $request->consumerName;
                $response['apartmentName'] = $apartName;
                $response['mobileNo'] = $request->mobileNo;
                $response['ps'] = $request->ps;
                $response['landmark'] = $request->landmark;
                $response['houseNo'] = $request->houseNo;
                $response['address'] = $request->address;
                $response['locality'] = $request->locality;
                $response['pinCode'] = $request->pinCode;
                $response['consumerCategory'] = consumerCategory::select('name')->first()->name;
                $response['consumerType'] = $consumerType->name;
                $response['demandFrom'] = $request->demandFrom;
                $response['demandDetails'] = $demand;
                $response['appliedBy'] = $userId;
                $response['appliedDate'] = date('Y-m-d');
                $msg = "Reanter created and demand generated successfully";
            }else{
                $msg = "Renter already exist";
            }

            return response()->json(['status' => true, 'data' => $response, 'msg' => $msg], 200);
        } catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
    }

    public function makePayment(Request $request)
    {
        
        try 
        {
            if(isset($request->consumerId))
            {
                $userId = 1;
                $consumerId = $request->consumerId;
                $totalPayableAmt = $request->paidAmount;
                $transcationDate = date('Y-m-d');
                $date_time = date("Y-m-d H:i:s");
                $paidUpto = date('Y-m-d', strtotime($request->paidUpto));
                
                $consumer = Consumer::select('tbl_consumer.*', 'a.apt_name', 'a.apt_code')
                                    ->where('tbl_consumer.id', $consumerId)
                                    ->leftjoin('tbl_apt_details_mstr as a', 'tbl_consumer.apt_mstr_id', '=', 'a.id')
                                    ->first();
                $totalDemandAmt = Demand::where('consumer_id', $consumerId)
                                ->where('paid_status', 0)
                                ->sum('total_tax');
                
                $remainingAmt = $totalDemandAmt - $totalPayableAmt;

                $transcation = Transaction::where('consumer_id', $consumerId);

                $lastpayment = $transcation->select('total_payable_amt')->where('pad_status', '1')->orderBy('id', 'desc')->first();
                
                $transcation = $transcation->whereDate('transaction_date','=', $transcationDate)
                                            ->where('total_payable_amt', $totalPayableAmt)
                                            ->get();
                $paidStatus = 1;
                
                if($request->paymentMode == 'Cheque')
                    $paidStatus = 2;

                if($transcation->count() == 0 && $totalPayableAmt > 0 )
                {
                    $trans = new Transaction();
                    $trans->setConnection($this->schema);
                    $trans->transaction_date = $transcationDate;
                    $trans->total_demand_amt = $totalDemandAmt;
                    $trans->total_payable_amt = $totalPayableAmt;
                    $trans->total_remaining_amt = $remainingAmt;
                    $trans->discount = 0;
                    $trans->penalty = 0;
                    $trans->payment_mode = $request->paymentMode;
                    $trans->pad_status = $paidStatus;
                    $trans->consumer_id = $consumerId;
                    $trans->user_id = $userId;
                    $trans->ip_address = $request->ip();
                    $trans->stamp_date = $date_time;
                    $trans->save();
                    
                    if($trans->id > 0)
                    {
                        $trans->transaction_no = $userId.date("dmY").$trans->id;
                        $trans->save();

                        if($request->paymentMode == 'Cheque')
                        {
                            $transdtls = new TransactionDetails();
                            $transdtls->setConnection($this->schema);
                            $transdtls->consumer_id = $consumerId;
                            $transdtls->transaction_id = $trans->id;
                            $transdtls->bank_name = $request->bankName;
                            $transdtls->branch_name = $request->branchName;
                            $transdtls->cheque_no = $request->chequeNo;
                            $transdtls->cheque_date = $request->chequeDate;
                            $transdtls->apt_mstr_id = $consumer->apt_mstr_id;
                            $transdtls->save();
                        }

                        $sql = "INSERT INTO tbl_collection (consumer_id, demand_id, transaction_id, total_tax, payment_from, payment_to, user_id, stamdate)
                        SELECT consumer_id, id, '".$trans->id."', total_tax, payment_from, payment_to, '".$userId."', '".$date_time."' FROM tbl_demand 
                        WHERE consumer_id='$consumerId' and (payment_to <='".$paidUpto."') and paid_status='0'";
                        
                        DB::connection($this->schema)->select($sql);

                        Demand::where('consumer_id', $consumerId)
                                ->where('payment_to', '<=', $paidUpto)
                                ->update(['paid_status' => 1]);
                        
                        $response['consumerName'] = $consumer->name;
                        $response['consumerNo'] = $consumer->consumer_no;
                        $response['apartmentName'] = $consumer->apt_name;
                        $response['apartmentCode'] = $consumer->apt_code;
                        $response['transactionId'] = $trans->id;
                        $response['transactionDate'] = $transcationDate;
                        $response['transactionNo'] = $userId.date("dmY").$trans->id;
                        $response['holdingNo'] = $consumer->holding_no;
                        $response['mobileNo'] = $consumer->mobile_no;
                        $response['monthlyRate'] = '';
                        $response['demandAmount'] = $totalDemandAmt;
                        $response['receivedAmount'] = $totalPayableAmt;
                        $response['remainingAmount'] = $remainingAmt;
                        $response['paidUpto'] = $request->paidUpto;
                        $response['previousPaidAmount'] = $lastpayment->total_payable_amt;
                        return response()->json(['status'=> True, 'data'=>$response, 'msg'=> 'Payment Done Successfully'], 200);
                    }
                    
                }
                else{
                    return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'This user payment today not updated..'], 200);
                }
            }
            
        } 
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
    }


    public function GeoLocation(Request $request)
    {
        try
        {   
            $response = array();
            if(isset($request->consumerId))
            {
                $geoLocation = GeoLocation::select('latitude', 'longitude')
                                            ->where('consumer_id', $request->consumerId)
                                            ->first();
                
                $consumer = Consumer::select('tbl_consumer.ward_no', 'tbl_consumer.name', 'apt_name', 'consumer_no', 'a.apt_code')
                                    ->leftjoin('tbl_apt_details_mstr as a', 'tbl_consumer.apt_mstr_id', 'a.id')
                                    ->where('tbl_consumer.id', $request->consumerId)
                                    ->first();
                
                
                $response['wardNo'] = $consumer->ward_no;
                $response['consumerName'] = $consumer->name;
                $response['consumerNo'] = $consumer->consumer_no;
                $response['apartmentName'] = $consumer->apt_name;
                $response['apartmentCode'] = $consumer->apt_code;
                $response['latitude'] = ($geoLocation)?$geoLocation->latitude:"";
                $response['longitude'] = ($geoLocation)?$geoLocation->longitude:"";
                
                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
                
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function TransactionModeChange(Request $request)
    {
        try
        {
            if(isset($request->transactionNo))
            {
                $trans = Transaction::where('transaction_no', $request->transactionNo)->first();
                if($trans == null)
                {
                    return response()->json(['status'=> False, 'data'=>'', 'msg'=> 'No Transaction detail fond on this tranc'], 200);
                }

                if(($trans->payment_mode == 'Cheque' && $trans->pad_status == '2') || $trans->payment_mode == 'Cash')
                {
                    $trans->payment_mode = $request->mode;
                    if($request->mode == 'Cheque')
                        $trans->pad_status = 2;
                    else
                        $trans->pad_status = 1;
                    $trans->save();
                    
                    if($request->mode == 'Cheque')
                    {
                        $transdtls = new TransactionDetails();
                        $transdtls->setConnection($this->schema);
                        $transdtls->consumer_id = $trans->consumer_id;
                        $transdtls->transaction_id = $trans->id;
                        $transdtls->bank_name = $request->bankName;
                        $transdtls->branch_name = $request->branchName;
                        $transdtls->cheque_no = $request->chequeNo;
                        $transdtls->cheque_date = $request->chequeDate;
                        $transdtls->apt_mstr_id = $trans->apt_mstr_id;
                        $transdtls->save();
                    }

                    return response()->json(['status'=> False, 'data'=>'', 'msg'=> 'Payment Mode changed successfully'], 200);
                }else{
                    return response()->json(['status'=> False, 'data'=>'', 'msg'=> 'Payment Mode can not change'], 200);
                }

                
            }
        }
        catch(Exception $e)
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
    }

    public function AllTransaction(Request $request)
    {
        try
        {   
            $response = array();
            if(isset($request->wardNo) && isset($request->userId))
            {
                $transactions = Transaction::select('transaction_date','tbl_transaction.transaction_no', 'total_payable_amt', 'c.name', 'c.consumer_no','cn.name as consumer_name', 'cn.consumer_no as consumer_no1')
                                    ->leftjoin('tbl_consumer as c', 'tbl_transaction.consumer_id', 'c.id')
                                    ->leftjoin('tbl_consumer as cn', 'tbl_transaction.apt_mstr_id', 'cn.apt_mstr_id')
                                    ->where('c.ward_no', $request->wardNo)
                                    ->where('tbl_transaction.user_id', $request->userId);
                                    

                if($request->date)
                    $transactions = $transactions->whereDate('transaction_date','=', date('Y-m-d', strtotime($request->date)));
                
                $transactions = $transactions->get();
                
                foreach($transactions as $trans)
                {
                    $val['consumerName'] = $trans->name;
                    $val['Amount'] = $trans->total_payable_amt;
                    $val['transactionDate'] = date('d-m-Y', strtotime($trans->transaction_date));
                    $val['consumerNo'] = $trans->consumer_no;
                    $val['transactionNo'] = $trans->transaction_no;
                    $response[] = $val;
                }
                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
                
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        }
        catch(Exception $e)
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
    }

    public function AllCollectionSummary(Request $request)
    {
        try
        {   
            $response = array();
            if(isset($request->userId))
            {
                $sql = "SELECT t.user_id, name, user_type, contactno, sum(total_payable_amt) as total_amt,
                sum(CASE when payment_mode = 'Cash' then total_payable_amt else 0 end) as cash_amount,
                sum(CASE when payment_mode = 'Cheque' then total_payable_amt else 0 end) as cheque_amount,
                sum(CASE when payment_mode = 'Paytm' then total_payable_amt else 0 end) as paytm_amount
                FROM tbl_transaction as t
                JOIN db_master.view_user_mstr as u on t.user_id=u.id
                WHERE t.user_id=".$request->userId." and pad_status in(1,2) group by t.user_id";
                
                $collection = DB::connection($this->schema)->select($sql);
                
                if($collection)
                {
                    $collection = $collection[0];
                
                    $response['tcName'] = $collection->name;
                    $response['designation'] = $collection->user_type;
                    $response['mobileNo'] = $collection->contactno;
                    $response['cash'] = $collection->cash_amount;
                    $response['cheque'] = $collection->cheque_amount;
                    $response['paytm'] = $collection->paytm_amount;
                    $response['totalAmount'] = $collection->paytm_amount;
                }
                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
                
            }else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        }
        catch(Exception $e)
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
    }

    

}