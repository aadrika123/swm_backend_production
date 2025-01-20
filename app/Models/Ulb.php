<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulb extends Model
{
    use HasFactory;
    // public $timestamps = false;
    // protected $table = 'tbl_ulb_list';

    public $timestamps = false;
    protected $connection = 'pgsql_master';
    protected $table = 'ulb_masters';
}
