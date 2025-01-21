<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginDetail extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'pgsql_master';
    protected $table = 'tbl_user_login_details';
}
