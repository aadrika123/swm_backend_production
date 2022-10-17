<?php

namespace App\Models;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;
    protected $connection;
    public $timestamps = false;
    protected $table = 'tbl_apt_details_mstr';
    
    public function __construct($data = null)
    {
        $this->connection = $data;
        //$this->connection = 'db_ranchi';
    }


    
}
