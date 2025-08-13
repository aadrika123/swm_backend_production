<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazorpayResponse extends Model
{
    use HasFactory;
    protected $connection;
    public $timestamps = false;
    protected $table = 'razorpay_responses';

    public function __construct($data = null)
    {
        $this->connection = $data;
    }
    public function saveResponseData($req, $data)
    {
        $razorpay_response = new RazorpayResponse();
        $razorpay_response->request_id       = $req['request_id'];
        $razorpay_response->order_id         = $req['payment_order_id'];
        $razorpay_response->consumer_id      = $req['consumerId'];
        $razorpay_response->amount           = $req['amount'];
        $razorpay_response->ulb_id           = $req['ulb_id'] ?? 11;
        $razorpay_response->ip_address       = $req->ip();
        $razorpay_response->created_at       = date('Y-m-d H:i:s');
        $razorpay_response->updated_at       = date('Y-m-d H:i:s');
        $razorpay_response->save();
    }
}
