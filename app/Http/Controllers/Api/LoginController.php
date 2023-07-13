<?php

namespace App\Http\Controllers\Api;

use App\Biz\LoginBiz;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function Index(LoginRequest $request)
    {
        return LoginBiz::Login($request);
    }

    public function Check(Request $request)
    {
        return LoginBiz::Check($request);
    }
}
