<?php

namespace App\Models;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Repository\MyClass;

class ConsumerType extends Model
{
    use HasFactory;
    protected $connection;
    public $timestamps = false;
    protected $table = 'tbl_consumer_type';

    public function __construct($data = null)
    {
        $this->connection = $data;
    }
}
