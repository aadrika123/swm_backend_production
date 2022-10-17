<?php

namespace App\Models;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CosumerReminder extends Model
{
    use HasFactory;
    protected $connection;
    public $timestamps = false;
    protected $table = 'tbl_consumer_reminder';

    public function __construct($data = null)
    {
        //$this->connection = Session::get('ulb');
        $this->connection = $data;
    }

}
