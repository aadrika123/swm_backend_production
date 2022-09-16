<?php

namespace App\Repository\Authentications;

use App\Models\TblUserMstr;
use App\Repository\Authentications\iAuth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * | Created On-07-09-2022 
 * | Created By-Anshu Kumar
 * | Created For- all the Authentication operations like login, Register, ForgetPass etc.
 */
class AuthRepository implements iAuth
{
    
    public function login(Request $req)
    {
        // $req->validate([
        //     'userName' => 'required',
        //     'password' => 'required'
        // ]);

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
}
