<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;
    protected $connection = 'db_ranchi';
    public $timestamps = false;
    protected $table = 'tbl_apt_details_mstr';
    
    // public function GetSchema($ulb_id)
    // {
    //     if(isset($ulb_id))
    //     {
    //         $ulb = Ulb::find($ulb_id);
    //         return $ulb->db_name;
    //     }
    // }
}
