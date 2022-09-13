<?php

namespace App\Repository;

use App\Models\ConsumerCategory;
use App\Models\Ward;
use App\Models\Apartment;
use App\Models\ConsumerType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * | Created On-08-09-2022 
 * | Created By-
 * | Created For- Consumer related api 
 */
class MasterRepository
{
    private $schema = 'db_ranchi';
    
    public function getConsumerFormDate(Request $request)
    {

        try
        {   
            
            $responseData = array();
            $responseData['wardList'] = Ward::get();
            $responseData['consumerCategory'] = ConsumerCategory::get();
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
                $responseData['apartmentList'] = Apartment::where('ward_no', $request->wardNo)
                                                    ->get();
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function getConsumerTypeList(Request $request)
    {

        try
        {   
            $responseData = array();
            
            if(isset($request->id))
                $responseData['consumerTypeList'] = ConsumerType::where('consumer_category_id', $request->id)
                                                    ->get();
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }
    

    

}