<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\ReportRepository;

class ReportController extends Controller
{
    protected $report;

    public function __construct(ReportRepository $report)
    {
        $this->rep = $report;
    }

    public function GetReportData(Request $request)
    {
        return $this->rep->ReportData($request);
    }

    


}
