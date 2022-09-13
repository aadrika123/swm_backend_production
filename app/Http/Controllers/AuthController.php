<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Authentications\AuthRepository;

class AuthController extends Controller
{
    /**
     * | Created On-07-09-2022 
     * | Created By-Anshu Kumar
     * | Controller For the User Login and Register Credentials Operations
     */

    //  Initializing construct function for Repositoy
    protected $eloquentAuth;
    public function __construct(AuthRepository $eloquentAuth)
    {
        $this->Repository = $eloquentAuth;
    }

    // User Login
    public function login(Request $req)
    {
        return $this->Repository->login($req);
    }

    public function GetHomePageData(Request $req)
    {
        return $this->Repository->CurrentLoginData($req);
    }

    public function ChangePassword(Request $req)
    {
        return $this->Repository->ChangePassword($req);
    }

    public function Logout(Request $req)
    {
        return $this->Repository->logout($req);
    }
}
