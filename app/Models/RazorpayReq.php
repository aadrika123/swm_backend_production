<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazorpayReq extends Model
{
    use HasFactory;
    protected $connection;
    public $timestamps = false;
    protected $table = 'razorpay_reqs';

    public function __construct($data = null)
    {
        $this->connection = $data;
    }

    /**
     * | Save data for the payment requests
     * | @param
     */
    public function saveRequestData($request, $paymentFor, $temp, $refDetails)
    {
        // Extract only "data" from the full API response
        $paymentData = $temp->data ?? null;

        $RazorPayRequest = new RazorpayReq;
        $RazorPayRequest->consumer_id         = $request->consumerId ?? $request->applicationId;
        $RazorPayRequest->user_id             = Auth()->user()->id ?? 36;
        $RazorPayRequest->amount              = $request->amount ?? $refDetails['totalAmount'];
        $RazorPayRequest->demand_from_upto    = $request->demandFrom ? ($request->demandFrom . "--" . $request->demandUpto) : null;
        $RazorPayRequest->ip_address          = $request->ip();
        $RazorPayRequest->order_id            = $request->orderId ?? $paymentData->orderId;
        $RazorPayRequest->payment_type        = $paymentType ?? 'Online';
        $RazorPayRequest->currency            = $currency ?? 'INR';
        $RazorPayRequest->payment_status      = $paymentStatus ?? 0;
        $RazorPayRequest->created_at          = date('Y-m-d H:i:s');
        $RazorPayRequest->updated_at          = date('Y-m-d H:i:s');
        $RazorPayRequest->save();
    }
}
