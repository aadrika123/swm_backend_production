<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumerType extends Model
{
    use HasFactory;
    protected $connection = 'db_ranchi';
    public $timestamps = false;
    protected $table = 'tbl_consumer_type';
}
