<?php

namespace App\Http\Controllers;

use App\Models\ActiveCitizen;
use App\Models\OtpRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ThirdPartyController extends Controller
{
    //
    /**
     * | Send OTP for Use
     * | OTP for Changing PassWord using the mobile no 
     * | @param request
     * | @var 
     * | @return 
        | Serial No : 01
        | Working
        | Dont share otp 
     */
    public function sendOtp(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'mobileNo' => "required|digits:10|regex:/[0-9]{10}/", #exists:active_citizens,mobile|
                'type' => "nullable|in:Register,Forgot",
            ]
        );
        try {
            $mOtpRequest = new OtpRequest();
            if ($request->type == "Register") {
                $userDetails = ActiveCitizen::where('mobile', $request->mobileNo)
                    ->first();
                if ($userDetails) {
                    throw new Exception("Mobile no $request->mobileNo is registered to An existing account!");
                }
            }
            if ($request->type == "Forgot") {
                $userDetails = ActiveCitizen::where('mobile', $request->mobileNo)
                    ->first();
                if (!$userDetails) {
                    throw new Exception("Pleas check your mobile.no!");
                }
            }
            $generateOtp = $this->generateOtp();
            DB::beginTransaction();
            $mOtpRequest->saveOtp($request, $generateOtp);
            DB::commit();
            return response()->json(["status" => true, "message" => "Demand Details", "data" => $generateOtp], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'msg' => $e->getMessage()], 500);
        }
    }
    /**
     * | Generate Random OTP 
     */
    public function generateOtp()
    {
        // $otp = Carbon::createFromDate()->milli . random_int(100, 999);
        $otp = 123123;
        return $otp;
    }

    /**
     * | Verify OTP 
     * | Check OTP and Create a Token
     * | @param request
        | Serial No : 02
        | Working
     */
    public function verifyOtp(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'otp' => "required|digits:6",
                'mobileNo' => "required|digits:10|regex:/[0-9]{10}/"
            ]
        );
        try {
            # model
            $mOtpMaster     = new OtpRequest();
            $mActiveCitizen = new ActiveCitizen();

            # logi 
            DB::beginTransaction();
            $checkOtp = $mOtpMaster->checkOtp($request);
            if (!$checkOtp) {
                $msg = "OTP not match!";
                return response()->json(["status" => true, "message" => "Demand Details",], 200);
            }
            $checkOtp->delete();
            DB::commit();
            return response()->json(["status" => true, "message" => "Demand Details",], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'msg' => $e->getMessage()], 500);
        }
    }
}
