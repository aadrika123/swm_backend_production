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

    

    /**
     * Get consumer list according to mobile no or name.
     *
     * @param  $id, $consumerNo, $consumerName, $mobileNo
     * @return ConsumerRepository->ConsumerList
     */
    public function GetConsumerList(Request $req)
    {
        return $this->ConResp->ConsumerList($req); 
    }



    /**
     * Get apartment list.
     *
     * @param  $apartmentId, $apartmentName
     * @return ConsumerRepository->ApartmentList
     */
    public function GetApartmentList(Request $req)
    {
        return $this->ConResp->ApartmentList($req);
    }



    /**
     * Get apartment details by id.
     *
     * @param  $apartmentId
     * @return ConsumerRepository->getApartmentDetails
     */
    public function GetApartmentDetailsById(Request $req)
    {
        return $this->ConResp->getApartmentDetails($req);
    }



    /**
     * Add new consumer.
     *
     * @param  $request
     * @return ConsumerRepository->ConsumerAdd
     */
    public function postConsumerAdd(Request $req)
    {
        return $this->ConResp->ConsumerAdd($req);
    }



    /**
     * Data for add renter master setup.
     *
     * @param  $request
     * @return ConsumerRepository->RenterFormData
     */
    public function GetRenterFormData(Request $req)
    {
        return $this->ConResp->RenterFormData($req);
    }



    /**
     * Get consumer details by id.
     *
     * @param  $request
     * @return ConsumerRepository->EditConsumerDetailsById
     */
    public function getEditConsumerDetailsById(Request $req)
    {
        return $this->ConResp->EditConsumerDetailsById($req);
    }



    /**
     * Make consumer deactivate.
     *
     * @param  $request
     * @return ConsumerRepository->DeactivateConsumer
     */
    public function postDeactivateConsumer(Request $req)
    {
        return $this->ConResp->DeactivateConsumer($req);
    }



    /**
     * Get payment deatails.
     *
     * @param  $request
     * @return ConsumerRepository->PaymentData
     */
    public function getPaymentData(Request $req)
    {
        return $this->ConResp->PaymentData($req);
    }



    /**
     * Make payment.
     *
     * @param  $request
     * @return ConsumerRepository->makePayment
     */
    public function MakePayment(Request $req)
    {
        return $this->ConResp->makePayment($req);
    }



    /**
     * Get calculate demand amount by consumer.
     *
     * @param  $request
     * @return ConsumerRepository->CalculatedAmount
     */
    public function getCalculatedAmount(Request $req)
    {
        return $this->ConResp->CalculatedAmount($req);
    }



    /**
     * Get dashboard data.
     *
     * @param  $request
     * @return ConsumerRepository->DashboardData
     */
    public function getDashboardData(Request $req)
    {
        return $this->ConResp->DashboardData($req);
    }



    /**
     * Get transaction details by transction no.
     *
     * @param  $request
     * @return ConsumerRepository->GetTrancation
     */
    public function searchTransaction(Request $req)
    {
        return $this->ConResp->GetTrancation($req);
    }



    /**
     * Make transaction deactivate.
     *
     * @param  $request
     * @return ConsumerRepository->TransactionDeactivate
     */
    public function transactionDeactivate(Request $req)
    {
        return $this->ConResp->TransactionDeactivate($req);
    }



    /**
     * Add new Renter.
     *
     * @param  $request
     * @return ConsumerRepository->AddRenter
     */
    public function RenterForm(Request $req)
    {
        return $this->ConResp->AddRenter($req);
    }



    /**
     * Get geo location.
     *
     * @param  $request
     * @return ConsumerRepository->GeoLocation
     */
    public function GetGeoLocation(Request $req)
    {
        return $this->ConResp->GeoLocation($req);
    }



    /**
     * Get all transactions according to user and ward no.
     *
     * @param  $request
     * @return ConsumerRepository->AllTransaction
     */
    public function GetAllTransaction(Request $req)
    {
        return $this->ConResp->AllTransaction($req);
    }



    /**
     * Get Collection summery by the user.
     *
     * @param  $request
     * @return ConsumerRepository->AllCollectionSummary
     */
    public function AllCollectionSummary(Request $req)
    {
        return $this->ConResp->AllCollectionSummary($req);
    }

}