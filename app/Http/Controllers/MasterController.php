<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\MasterRepository;
use Exception;

class MasterController extends Controller
{
    /**
     * | Created On- 08-09-2022 
     * | Created By- 
     * | Controller Consumer related operations
     */

    //  Initializing construct function for Repositoy
    protected $master;
    public function __construct(MasterRepository $master)
    {
        $this->Mstr = $master;

    }

    public function GetConsumerAddFormData(Request $req)
    {
        return $this->Mstr->getConsumerFormDate($req);
    }

    public function GetApartmentListData(Request $req)
    {
        return $this->Mstr->getApartmentList($req);
    }

    public function GetConsumerType(Request $req)
    {
        return $this->Mstr->getConsumerTypeList($req);
    }
}
