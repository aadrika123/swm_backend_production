<?php

namespace App\Http\Controllers;

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
            $authUser = auth()->user();
            $fromDate = $req->fromDate ?? Carbon::now()->format('Y-m-d');
            $toDate   = $req->toDate   ?? Carbon::now()->format('Y-m-d');
            $mTblTcTracking = new TblTcTracking();

            $logDetail = $mTblTcTracking->listgeoLocation()
                ->whereBetween('created_at', [$fromDate . ' 00:00:01', $toDate . ' 23:59:59'])
                ->where('tbl_tc_trackings.status', true)
                ->where('tbl_tc_trackings.ulb_id', $authUser->current_ulb);

            if (isset($req->tcId))
                $logDetail = $logDetail->where('user_id', $req->tcId);

            $logDetail = $logDetail
                ->paginate($perPage);

            return $this->responseMsgs(true, "Tc Geolocation List", $logDetail);
        } catch (Exception $e) {
            return $this->responseMsgs(true,  $e->getMessage(), "");
        }
    }
}
