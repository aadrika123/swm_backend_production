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
}
