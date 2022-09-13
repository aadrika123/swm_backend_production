<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\ConsumerRepository;

class ConsumerController extends Controller
{
    /**
     * | Created On- 08-09-2022 
     * | Created By- 
     * | Controller Consumer related operations
     */

    //  Initializing construct function for Repositoy
    protected $consumer;
    public function __construct(ConsumerRepository $consumer)
    {
        $this->ConResp = $consumer;

    }

    public function GetConsumerList(Request $req)
    {
        return $this->ConResp->ConsumerList($req);
    }

    public function GetApartmentList(Request $req)
    {
        return $this->ConResp->ApartmentList($req);
    }

    public function GetApartmentDetailsById(Request $req)
    {
        return $this->ConResp->getApartmentDetails($req);
    }

    public function postConsumerAdd(Request $req)
    {
        return $this->ConResp->ConsumerAdd($req);
    }

    public function GetRenterFormData(Request $req)
    {
        return $this->ConResp->RenterFormData($req);
    }

    public function getEditConsumerDetailsById(Request $req)
    {
        return $this->ConResp->EditConsumerDetailsById($req);
    }

    public function postDeactivateConsumer(Request $req)
    {
        return $this->ConResp->DeactivateConsumer($req);
    }

    public function getPaymentData(Request $req)
    {
        return $this->ConResp->PaymentData($req);
    }

    public function MakePayment(Request $req)
    {
        return $this->ConResp->makePayment($req);
    }

    public function getCalculatedAmount(Request $req)
    {
        return $this->ConResp->CalculatedAmount($req);
    }

    public function getDashboardData(Request $req)
    {
        return $this->ConResp->DashboardData($req);
    }

    public function searchTransaction(Request $req)
    {
        return $this->ConResp->GetTrancation($req);
    }

    public function transactionDeactivate(Request $req)
    {
        return $this->ConResp->TransactionDeactivate($req);
    }

    public function RenterForm(Request $req)
    {
        return $this->ConResp->AddRenter($req);
    }

    public function GetGeoLocation(Request $req)
    {
        return $this->ConResp->GeoLocation($req);
    }

}