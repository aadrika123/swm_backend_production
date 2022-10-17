<?php

namespace App\Repository;

use App\Models\ConsumerCategory;
use App\Models\Ward;
use App\Models\Apartment;
use App\Models\ConsumerType;
use App\Models\ulb;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\Api\Helpers;


/**
 * | Created On-08-09-2022 
 * | Created By-
 * | Created For- Consumer related api 
 */
class MasterRepository
{
    use Helpers;

    protected $dbConn;
    protected $Apartment;
    protected $ConsumerType;
    protected $ConsumerCategory;
    protected $Ward;

    public function __construct(Request $request)
    {

        $this->dbConn = $this->GetSchema($request->bearerToken());

        $this->Ward = new Ward($this->dbConn);
        $this->Apartment = new Apartment($this->dbConn);
        $this->ConsumerType = new ConsumerType($this->dbConn);
        $this->ConsumerCategory = new ConsumerCategory($this->dbConn);
    }

    public function getConsumerFormDate(Request $request)
    {

        try
        {   
            
            $responseData = array();
            $responseData['wardList'] = $this->Ward->get();
            $responseData['consumerCategory'] = $this->ConsumerCategory->get();
            $responseData['initialDemandDate'] = "01-01-2022";
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function getApartmentList(Request $request)
    {

        try
        {   
            $responseData = array();
            if(isset($request->wardNo))
            {
                $aptlist = $this->Apartment->where('ward_no', $request->wardNo)
                                    ->orderBy('id', 'DESC')
                                    ->get();
            
            }else
                $aptlist = $this->Apartment->orderBy('id', 'DESC')->get();
            
            $responseData['apartmentList'] = $aptlist;
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function getApartmentById(Request $request)
    {

        try
        {   
            $responseData = array();
            
            if(isset($request->id))
            {
                $apt = $this->Apartment->where('id', $request->id)
                                                    ->first();
                if($apt)
                {
                    $responseData['id'] = $apt->id;
                    $responseData['aptName'] = $apt->apt_name;
                    //changed by talib
                    $responseData['wardNo'] = $apt->ward_no;
                    //changed by talib
                    $responseData['aptCode'] = $apt->apt_code;
                    $responseData['aptAddress'] = $apt->apt_address;
                }
            }
            
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function GetConsumerTypeByCategoryId(Request $request)
    {

        try
        {   
            $responseData = array();
            
            if(isset($request->id))
                $responseData['consumerTypeList'] = $this->ConsumerType->where('consumer_category_id', $request->id)
                                                    ->get();
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }
    

    public function updateApartment(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'wardNo' => 'required',
                'apartmentName' => 'required',
                'address' => 'required',
                'apartmentId' => 'required',
                //changed by talib
                'apartmentCode' => 'required',
                //changed by talib
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }

            if(isset($request->apartmentId))
            {
                
                $apartment = $this->Apartment->find($request->apartmentId);
                $apartment->ward_no  =  $request->wardNo; 
                $apartment->apt_name  =  $request->apartmentName;
                $apartment->apt_address  =  $request->address;
                //changed by talib
                $apartment->apt_code  =  $request->apartmentCode;
                //changed by talib
                $apartment->save();         
                
                return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Apartment updated successfully'], 200);
            }else
            {
                return response()->json(['status'=> False, 'data'=>'', 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function addApartment(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'wardNo' => 'required',
                'aptName' => 'required',
                'aptCode' => 'required',
                'aptAddress' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }

            
            $apartment = $this->Apartment;
            $apartment->ward_no  =  $request->wardNo; 
            $apartment->apt_name  =  $request->aptName;
            $apartment->apt_code  =  $request->aptCode;
            $apartment->apt_address  =  $request->aptAddress;
            $apartment->save();         
            // if($apartment->id)
            // {
            //     $apartment->apt_code  =  'APT-'.sprintf("%04d", $apartment->id);
            //     $apartment->save(); 
            //     return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Apartment updated successfully'], 200);
                
            // }
            return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Add new Apartment successfully'], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function getConsumerCategoryList(Request $request)
    {

        try
        {   
            $responseData = array();
            $records = $this->ConsumerCategory->orderBy('id', 'DESC')->get();
            
            foreach($records as $record)
            {
                $val['consumerCategory'] = $record->name;
                $val['ccId'] = $record->id;
                $responseData[] = $val;
            }

            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    
    public function ConsumerCategoryAdd(Request $request)
    {

        try
        {   

            $validator = Validator::make($request->all(), [
                'ulbId' => 'required',
                'consumerCategory' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }


            $responseData = array();
            $catgory = $this->ConsumerCategory;
            $catgory['name'] = $request->consumerCategory;
            $catgory->save();
            
            if($catgory->id)
                return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> 'Consumer Category Added successfully'], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function ConsumerCategoryUpdate(Request $request)
    {

        try
        {   

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'ulbId' => 'required',
                'consumerCategory' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }


            $responseData = array();
            $catgory = $this->ConsumerCategory->find($request->id);
            $catgory['name'] = $request->consumerCategory;
            $catgory->save();
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> 'Consumer Category Updated successfully'], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function ConsumerCategoryById(Request $request)
    {

        try
        {   
            $responseData = array();
            if(isset($request->id))
            {


                
                $catgory = $this->ConsumerCategory->find($request->id);
                if($catgory->id)
                {
                    $responseData['consumerCategory'] = $catgory->name;
                    $responseData['id'] = $catgory->id;
                
                
                    $msg = "Consumer Category Updated successfully";
                }else
                    $msg = "Record Not found";
                
                return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> $msg], 200);
            }
            else{
                return response()->json(['status'=> False, 'data'=>$responseData, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function GetConsumerTypeList(Request $request)
    {

        try
        {   
            $responseData = array();
            
            $contypes = $this->ConsumerType->select('tbl_consumer_type.*', 'c.name as cat_name')
                                                            ->join('tbl_consumer_category as c', 'tbl_consumer_type.consumer_category_id', '=', 'c.id')
                                                            ->orderBy('tbl_consumer_type.id', 'DESC')
                                                            ->get();
            
            foreach($contypes as $contype)
            {
                $val['id'] = $contype->id;
                $val['consumerType'] = $contype->name;
                $val['consumerCategory'] = $contype->cat_name;
                $val['rate'] = $contype->rate;
                $responseData[] = $val;
            }
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }



    public function ConsumerTypeAdd(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'consumerType' => 'required',
                'consumerCategory' => 'required',
                'rate' => 'required',
                'ulbId' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            

            $conType = $this->ConsumerType;
            $conType->consumer_category_id  =  $request->consumerCategory; 
            $conType->name  =  $request->consumerType;
            $conType->rate  =  $request->rate;
            $conType->save();         
            
            if($conType->id)
                return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Consumer Type Added successfully'], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function ConsumerTypeUpdate(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'consumerType' => 'required',
                'consumerCategory' => 'required',
                'rate' => 'required',
                'ulbId' => 'required',
                'id' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            

            
                
            $conType = $this->ConsumerType->find($request->id);
            $conType->consumer_category_id  =  $request->consumerCategory; 
            $conType->name  =  $request->consumerType;
            $conType->rate  =  $request->rate;
            $conType->save();         
            
            return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Consumer Type Updated successfully'], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function ConsumerTypeById(Request $request)
    {

        try
        {   
            $responseData = array();
            if(isset($request->id))
            {


                
                $contype = $this->ConsumerType->select('tbl_consumer_type.*', 'c.name as cat_name')
                                        ->join('tbl_consumer_category as c', 'tbl_consumer_type.consumer_category_id', '=', 'c.id')
                                        ->where('tbl_consumer_type.id', $request->id)
                                        ->first();
                if($contype->id)
                {
                    $responseData['id'] = $contype->id;
                    $responseData['ulbId'] = $request->ulbId;
                    $responseData['consumerType'] = $contype->name;
                    $responseData['consumerCategory'] = $contype->cat_name;
                    $responseData['rate'] = $contype->rate;
                
                
                    $msg = "";
                }else
                    $msg = "Record Not found";
                
                return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> $msg], 200);
            }
            else{
                return response()->json(['status'=> False, 'data'=>$responseData, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function UlbList(Request $request)
    {

        try
        {   
            $responseData = array();
            
            $ulblist = ulb::orderBy('id', 'DESC')->get();
            
            foreach($ulblist as $ulb)
            {
                $val['id'] = $ulb->id;
                $val['ulbName'] = $ulb->ulb_name;
                $responseData[] = $val;
            }
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function UlbAdd(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'ulbName' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            
            $ulbshortname = explode(" ", $request->ulbName);
            $ulbshortname = $ulbshortname[0];
            
            $ulbdata = new ulb();
            $ulbdata->ulb_name  =  $request->ulbName; 
            $ulbdata->ulb  =  $ulbshortname;
            $ulbdata->db_name  =  "db_".strtolower($ulbshortname);
            $ulbdata->status = 1;
            $ulbdata->save();         
            
            if($ulbdata->id)
                return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Ulb Added successfully'], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function UlbUpdate(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'ulbName' => 'required',
                'id' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            
            $ulbshortname = explode(" ", $request->ulbName);
            $ulbshortname = $ulbshortname[0];
            
            $ulbdata = ulb::find($request->id);
            $ulbdata->ulb_name  =  $request->ulbName; 
            $ulbdata->ulb  =  $ulbshortname;
            $ulbdata->db_name  =  "db_".strtolower($ulbshortname);
            $ulbdata->save();         
            
            return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Ulb Updated successfully'], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function UlbActiveDeactive(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'toggleStatus' => 'required',
                'id' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            

            $ulbdata = ulb::find($request->id);
            $ulbdata->status = $request->toggleStatus;
            $ulbdata->save();
            
            $msg = 'Ulb Activated successfully';
            if($request->toggleStatus == 0)
                $msg = 'Ulb Deactivated successfully';
                
            
            return response()->json(['status'=> True, 'data'=>'', 'msg'=> $msg], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function UlbById(Request $request)
    {

        try
        {   
            $responseData = array();
            
            if(isset($request->ulbId))
            {
                $ulb = ulb::find($request->ulbId);

                $responseData['id'] = $ulb->id;
                $responseData['ulbName'] = $ulb->ulb_name;
                
                return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
            }else{
                return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function WardList(Request $request)
    {

        try
        {   
            $responseData = array();
            
            $wards = $this->Ward->orderBy('id', 'DESC')->get();
            
            foreach($wards as $ward)
            {
                $val['id'] = $ward->id;
                $val['wardNo'] = $ward->name;
                $responseData[] = $val;
            }
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }



    public function WardAdd(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'ulbId' => 'required',
                'wardNo' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            
                
            $ward = $this->Ward;
            $ward->name  =  $request->wardNo; 
            $ward->save();         
            
            if($ward->id)
                return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Ward Added successfully'], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function WardUpdate(Request $request)
    {

        try
        {  
            $validator = Validator::make($request->all(), [
                'wardNo' => 'required',
                'ulbId' => 'required',
                'id' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            
                
            $ward = $this->Ward->find($request->id);
            $ward->name  =  $request->wardNo; 
            $ward->save();         
            
            return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Consumer Type Updated successfully'], 200);  
           
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function WardById(Request $request)
    {

        try
        {   
            $responseData = array();
            if(isset($request->wardId))
            {

                
                $ward = $this->Ward->find($request->wardId);
                if($ward->id)
                {
                    $responseData['wardId'] = $ward->id;
                    $responseData['wardname'] = $ward->name;
                    $msg = '';
                }else
                    $msg = "Record Not found";
                
                return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> $msg], 200);
            }
            else{
                return response()->json(['status'=> False, 'data'=>$responseData, 'msg'=> 'Undefined parameter supply'], 200);
            }
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    

}