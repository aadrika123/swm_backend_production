<?php

namespace App\Repository\Authentications;

use Illuminate\Http\Request;

/**
 * | Created On-07-09-2022 
 * | Created By-Anshu Kumar
 * | The Authentication Interface for Auth Repository
 */
interface iAuth
{
    public function login(Request $request);
}
