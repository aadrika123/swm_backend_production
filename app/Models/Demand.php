<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    use HasFactory;
    protected $connection;
    public $timestamps = false;
    protected $table = 'swm_demands';


    public function __construct($data = null)
    {
        //$this->connection = Session::get('ulb');
        $this->connection = $data;
    }

       /**
     * | Get Demand According to consumerId and payment status false 
     */
    public function getFirstConsumerDemand($consumerId)
    {
        return Demand::where('consumer_id', $consumerId)
            ->where('paid_status', 0)
            ->orderByDesc('id');
    }
}
