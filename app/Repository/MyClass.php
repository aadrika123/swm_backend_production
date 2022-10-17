<?php

namespace App\Repository;


use Exception;
use Illuminate\Http\Request;
use App\Traits\Api\Helpers;



/**
 * | Created On-09-24-2022 
 * | Created By-
 * | Created For- Report related api 
 */
class MyClass
{
    
    protected static $staticVar = 'value';

    public function setValue($input) {
        return $this->staticVar = $input;
    }

    public function getValue() {
        return $this->staticVar;
    }



}