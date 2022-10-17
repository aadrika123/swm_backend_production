<?php
namespace App\Repository;


Class DbCon
{
    public static $db = null;


    public static function getDb($dbname = null)
    {
        if(is_null(Self::$db) && !is_null($dbname))
        {
            echo "aaaa";
            Self::$db = $dbname;
        }
        return Self::$db.">";
    }
}