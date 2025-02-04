<?php

namespace App\Http\Controllers;

use App\Models\TblTcTracking;
use App\Models\UserLoginDetail;
use Illuminate\Http\Request;
use App\Repository\iReportRepository;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class ReportController extends Controller
{
    protected $report;

    public function __construct(iReportRepository $report)
    {
        $this->rep = $report;
    }

    public function GetReportData(Request $request)
    {
        return $this->rep->ReportData($request);
    }

    public function GetDemandReceiptData(Request $request)
    {
        return $this->rep->DemandReceipt($request);
    }

    /**
     * | Add TC Geo Location
     */
    public function addTcGeoLocation(Request $req)
    {
        $validator = Validator::make(
            $req->all(),
            [
                "latitude"    => "required",
                "longitude"   => "required",
            ]
        );

        if ($validator->fails())
            return response()->json([
                'status' => false,
                'msg'    => $validator->errors()->first(),
                'errors' => "Validation Error"
            ], 200);
        try {

            $user = Auth()->user();
            $mTblTcTracking = new TblTcTracking();

            $metaReqs = [
                'user_id'   => $user->id,
                'ulb_id'    => $user->ulb_id,
                'latitude'  => $req->latitude,
                'longitude' => $req->longitude,
            ];

            $mTblTcTracking->createGeoLocation($metaReqs);

            return response()->json(['status' => true,  'msg' => ''], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'data' => '', 'msg' => $e->getMessage()], 400);
        }
    }
    /**
     * | Tc Geolocation List
     */
    public function tcGeolocationList(Request $req)
    {
        $validator = Validator::make(
            $req->all(),
            [
                "fromDate"   => "nullable",
                "toDate"     => "nullable",
            ]
        );

        if ($validator->fails())
            return response()->json([
                'status' => false,
                'msg'    => $validator->errors()->first(),
                'errors' => "Validation Error"
            ], 200);
        try {
            $perPage = $req->perPage ?? 10;
            $user = Auth()->user();
            $fromDate = $req->fromDate ?? Carbon::now()->format('Y-m-d');
            $toDate   = $req->toDate   ?? Carbon::now()->format('Y-m-d');
            $mTblTcTracking = new TblTcTracking();

            $logDetail = $mTblTcTracking->listgeoLocation()
                ->whereBetween('tbl_tc_trackings.created_at', [$fromDate . ' 00:00:01', $toDate . ' 23:59:59'])
                ->where('tbl_tc_trackings.status', true)
                ->where('tbl_tc_trackings.ulb_id', $user->ulb_id);

            if (isset($req->tcId))
                $logDetail = $logDetail->where('user_id', $req->tcId);

            $logDetail = $logDetail
                ->paginate($perPage);

            return response()->json(['status' => true, 'data' => $logDetail,  'msg' => ''], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'data' => '', 'msg' => $e->getMessage()], 400);
        }
    }
}
