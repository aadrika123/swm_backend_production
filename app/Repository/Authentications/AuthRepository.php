<?php

namespace App\Repository\Authentications;

use App\Models\TblUserMstr;
use App\Models\UserDetails;
use App\Models\UserType;
use App\Models\UserWardPermission;
use App\Models\Ward;
use App\Models\Ulb;
use App\Repository\Authentications\iAuth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\Api\Helpers;

/**
 * | Created On-07-09-2022 
 * | Created By-Anshu Kumar
 * | Created For- all the Authentication operations like login, Register, ForgetPass etc.
 */
class AuthRepository implements iAuth
{
    use Helpers;
    public function login(Request $req)
    {

        
        try {
            $validator = Validator::make($req->all(), [
                'userName' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }
            $refUserName = TblUserMstr::where('user_name', $req->userName)
                ->first();
            // If the Username is existing
            if ($refUserName) {
                // Checking Password
                $passStmt = $refUserName->user_password == md5($req->password);
                if ($passStmt == true) {
                    $token = $refUserName->createToken('my-app-token')->plainTextToken;
                    $refUserName->remember_token = $token;
                    $refUserName->save();

                    $response = ['status' => True, 'loginstatus' => 1, 'msg' => 'You Have Logged In', 'data' => $token, 'userId' => $refUserName->id];
                    return response($response, 200);
                }
                // If Password Does not Matched
                if ($passStmt == false) {
                    $response = ['status' => True, 'loginstatus' => 0 ,'msg' => 'Incorrect Password', 'data' => ''];
                    return response($response, 200);
                }
            }
            // If the UserName is not Existing
            if (!$refUserName) {
                $message = ['status' => True, 'loginstatus' => 0, 'msg' => 'UserName not Found', 'data' => ''];
                return response()->json($message, 200);
            }
        } catch (Exception $e) {
            return response()->json(['status'=> False, 'loginstatus' => 0, 'data'=>'', 'msg'=> $e], 400);
        }
    }

    public function CurrentLoginData(Request $req)
    {
        try
        {
            $response = array();
            if($req->userId)
            {
                $lastLogin = DB::table('view_user_mstr')
                        ->where('id', $req->userId)
                        ->where('status', 1)
                        ->first();
                $response['userName'] = $lastLogin->name;
                $response['designation'] = $lastLogin->user_type;
                $response['mobileNo'] = $lastLogin->contactno;
                $response['address'] = $lastLogin->address;
                $response['image'] = $lastLogin->photo_path;
                $response['lastVisitedTime'] = $lastLogin->login_time;
                $response['lastVisitedDate'] = date('d-m-Y', $lastLogin->login_date);
                $response['lastIpAddress'] = $lastLogin->ip_address;
                
                return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
            }
            else{
                return response()->json(['status'=> False, 'data'=>$response, 'msg'=> 'Undefined parameter supply'], 200);
            }
        }
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function ChangePassword(Request $req)
    {
        

        try {

            $validator = Validator::make($req->all(), [
                'userName' => 'required',
                'oldPassword' => 'required',
                'newPassword' => 'required',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }

            $refUserName = TblUserMstr::where('user_name', $req->userName)
                                        ->first();
            // If the Username is existing
            if ($refUserName) {
                // Checking Password
                $passStmt = $refUserName->user_password == md5($req->oldPassword);
                if ($passStmt == true) {
                    $refUserName->user_password = md5($req->newPassword);
                    $refUserName->save();

                    $response = ['status' => true, 'msg' => 'Changed password successfully', 'data' => ''];
                    return response($response, 200);
                }
                // If Password Does not Matched
                if ($passStmt == false) {
                    $response = ['status' => true, 'msg' => 'Incorrect old password', 'data' => ''];
                    return response($response, 200);
                }
            }
            // If the UserName is not Existing
            if (!$refUserName) {
                $message = ['status' => true, 'msg' => 'UserName not Found', 'data' => ''];
                return response()->json($message, 200);
            }
        } catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
    }

    public function logout(Request $request) 
    {
        try{
            Auth::logout();
            return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'You are login out'], 200);
        }
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function CreateUser(Request $req)
    {
        

        try 
        {
            $validator = Validator::make($req->all(), [
                'name' => 'required',
                'contactNo' => 'required',
                'address' => 'required',
                'userType' => 'required',
                'photo' => 'mimes:jpeg,png,jpg,png|max:200',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }

            $userId= $req->userId;
            $password = md5(12345);
			$date = date('Y-m-d H:i:s');
            
            $userDtl = new UserDetails();
            $userDtl->name = $req->name;
            $userDtl->contactno = $req->contactNo;
            $userDtl->address = $req->address;
            $userDtl->save();

            if($userDtl->id)
            {
                $userType = UserType::find($req->userType);
                $fname = explode(" ",$req->name);
                
                $user = new TblUserMstr();

                $user->user_det_id = $userDtl->id;
                $user->user_type_id = $req->userType;
                $user->user_password = $password;
                $user->ip_address = $req->ip();
                $user->user_id = $userId;
                $user->stampdate = $date;
                $user->status = 1;
                
                $user->original_pass = 12345;
                $user->save();

                if($user->id)
                {
                    $filePath = '';
                    if(!empty($req->photo))
                    {
                        $filePath = md5($userDtl->id).'.'.$req->photo->extension();
                        $req->photo->move(public_path('uploads/user'), $filePath);
                    }

                    $username = $fname[0]."_".$userType->short_name."_".$user->id;
                    $user->user_name = $username;
                    $user->photo_path = $filePath;
                    $user->save();

                    
                    if(isset($req->wards) && isset($req->ulbId))
                    {
                        $wardarray = explode(',', $req->wards);
                        foreach($wardarray as $key=>$value)
                        {
                            $permission  = new UserWardPermission();
                            $permission->user_det_id = $userDtl->id;
                            $permission->user_id = $user->id;
                            $permission->ulb_id = $req->ulbId;
                            $permission->ward_id = $value;
                            $permission->save();
                        }
                    }

                    if(isset($req->ulbs))
                    {
                        foreach($req->ulbs as $ulb)
                        {
                            $permission  = new UserWardPermission();
                            $permission->user_det_id = $userDtl->id;
                            $permission->user_id = $user->id;
                            $permission->ulb_id = $ulb;
                            $permission->save();
                        }
                    }

                    return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'User created successfully. user name:'.$username.' and temporary password:12345'], 200);
                }
                
            }else{
                return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Employee created but user not created due to technical issue'], 200);
            }

        }
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function UpdateUser(Request $req)
    {
        

        try 
        {
            $validator = Validator::make($req->all(), [
                'userId' => 'required',
                'name' => 'required',
                'contactNo' => 'required',
                'address' => 'required',
                'userType' => 'required',
                'photo' => 'mimes:jpeg,png,jpg,png|max:200',
            ]);
            if ($validator->fails()) {    
                return response()->json(['status'=> False, 'msg' => $validator->messages()]);
            }

            $userId= 1;
			$date = date('Y-m-d H:i:s');
            
            $userDtl = UserDetails::select('tbl_user_details.*')->join('tbl_user_mstr as u', 'u.user_det_id', '=', 'tbl_user_details.id')->where('u.id', $req->userId)->first();
            $userDtl->name = $req->name;
            $userDtl->contactno = $req->contactNo;
            $userDtl->address = $req->address;
            $userDtl->save();

            if($userDtl->id)
            {

                $filePath = '';
                if(!empty($req->photo))
                {
                    $filePath = md5($userDtl->id).'.'.$req->photo->extension();
                    $req->photo->move(public_path('uploads/employee'), $filePath);
                }
                
                $user = TblUserMstr::find($req->userId);
                $user->user_type_id = $req->userType;
                $user->ip_address = $req->ip();
                $user->user_id = $userId;
                $user->stampdate = $date;
                $user->photo_path = $filePath;
                $user->save();

                if($user->id)
                {
                    
                    if(isset($req->wards) && isset($req->ulbId))
                    {
                        $wardarray = explode(',', $req->wards);
                        UserWardPermission::where('user_id', $req->userId)
                                            ->where('ulb_id', $req->ulbId)
                                            ->whereNotNull('ward_id')
                                            ->update(['stts' => 0]);
                        
                        foreach($wardarray as $key=>$value)
                        {
                            
                            $permission = UserWardPermission::where('user_id', $req->userId)
                                                            ->where('ulb_id', $req->ulbId)
                                                            ->where('ward_id', $value)
                                                            ->first();
                            
                            if($permission)
                            {
                                $permission->stts = 1;
                                $permission->save();
                            }
                            else{

                                $perm  = new UserWardPermission();
                                $perm->user_det_id = $userDtl->id;
                                $perm->user_id = $user->id;
                                $perm->ulb_id = $req->ulbId;
                                $perm->ward_id = $value;
                                $perm->save();
                            }

                        }
                    }

                    if(isset($req->ulbs))
                    {
                        $ulbarray = explode(',', $req->ulbs);
                        UserWardPermission::where('user_id', $req->userId)
                                            ->whereNull('ward_id')
                                            ->update(['stts' => 0]);
                                                            
                        foreach($ulbarray as $key=>$value)
                        {
                            $permission = UserWardPermission::where('user_id', $req->userId)
                                                            ->where('ulb_id', $value)
                                                            ->whereNull('ward_id')
                                                            ->first();
                            
                            if($permission)
                            {
                                $permission->stts = 1;
                                $permission->save();
                            }
                            else{

                                $perm  = new UserWardPermission();
                                $perm->user_det_id = $userDtl->id;
                                $perm->user_id = $user->id;
                                $perm->ulb_id = $value;
                                $perm->save();
                            } 
                        }
                    }

                    return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'User updated successfully.'], 200);
                }
                
            }else{
                return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'Employee not updated'], 200);
            }

        }
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function getAllUser(Request $req)
    {
        try
        {
            $response = array();

            $allUser = DB::table('view_user_mstr');

            if($req->userId)
            {
                $allUser = $allUser->where('id', $req->userId);
            }
            $allUser = $allUser->get();
            
            foreach($allUser as $user)
            {
                $val['userId'] = $user->id;
                $val['userName'] = $user->name;
                $val['designation'] = $user->user_type;
                $val['mobileNo'] = $user->contactno;
                $val['address'] = $user->address;
                $val['image'] = public_path('uploads\user').$user->photo_path;
                $val['lastVisitedTime'] = $user->login_time;
                $val['lastVisitedDate'] = date('d-m-Y', $user->login_date);
                $val['lastIpAddress'] = $user->ip_address;
                $val['status'] = ($user->status == 1)?'Active':'Deactive';
                $val['ulbDetails'] = $this->GetUlbs($user->id);

                $response[] = $val;
            }
            
            return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
           
        }
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function makeUserActiveDeactive(Request $req)
    {
        try
        {

            if(isset($req->userId) && isset($req->status))
            {
                $user = TblUserMstr::find($req->userId);
                $user->status = $req->status;
                $user->save();

                return response()->json(['status'=> True, 'data'=>'', 'msg'=> 'user updated sucessfully'], 200);
            
            }else{
                return response()->json(['status'=> False, 'data'=>'', 'msg'=> 'Undefined parameter suppied or lack of information missing'], 200);
            }

        }
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }

    public function getUserFormDate(Request $request)
    {

        try
        {   
            
            $responseData = array();
            $responseData['wardList'] = Ward::get();
            $responseData['ulbList'] = Ulb::get();
            $responseData['userType'] = UserType::get();
            
            return response()->json(['status'=> True, 'data'=>$responseData, 'msg'=> ''], 200);
        } 
        catch (Exception $e) 
        {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }


    public function getTcList(Request $req)
    {
        try
        {
            $response = array();
            $whereparam = '';
            
            if(isset($req->ulbId))
            {
                $whereparam = ' and uw.ulb_id='.$req->ulbId;
            }
            
            $sql = "SELECT distinct name,uw.user_id,contactno,address FROM view_user_mstr um
            left join (select user_id,ulb_id from tbl_user_ward group by user_id,ulb_id) uw on uw.user_id=um.id 
            where user_type='Tax Collector' ". $whereparam;
            $allUser = DB::select($sql);
            
            
            foreach($allUser as $user)
            {
                $val['tcId'] = $user->user_id;
                $val['tcName'] = $user->name;
                $val['mobileNo'] = $user->contactno;
                $val['address'] = $user->address;
                $response[] = $val;
            }
            return response()->json(['status'=> True, 'data'=>$response, 'msg'=> ''], 200);
           
        }
        catch (Exception $e) {
            return response()->json(['status'=> False, 'data'=>'', 'msg'=> $e], 400);
        }
        
    }
    

}
