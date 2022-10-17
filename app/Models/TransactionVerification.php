<?php

namespace App\Models;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionVerification extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'tbl_transaction_verification';

    public function __construct($data = null)
    {
        //$this->connection = Session::get('ulb');
        $this->connection = $data;
    }
}
