<?php

namespace App\Biz;

use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use App\Utils\CommonUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoginBiz
{
    public static function Check(Request $request)
    {
        $username = $request->get('username');
        $key = sprintf(config('constants.USER_LOGIN_KEY'), $username);

        if (empty($username) || !Cache::has($key)) {
            return CommonUtils::RspError('请重新登录');
        }

        return CommonUtils::RspSuccess('');
    }

    public static function Login(LoginRequest $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $user = User::firstWhere('username', $username);
        if (empty($user)) {
            return CommonUtils::RspError('user username or password incorrect');
        }

        if (!password_verify($user->password, $password)) {
            return CommonUtils::RspError('user username or password incorrect');
        }

        $key = sprintf(config('constants.USER_LOGIN_KEY'), $username);
        Cache::put($key, $user->toJson(), config('constants.USER_LOGIN_EXPIRED'));

        return CommonUtils::RspSuccess([
            'username' => $username,
            'last_login_time' => date('Y-m-d H:i:s', $user['last_login_time']),
            'expire_time' => time() + config('constants.USER_LOGIN_EXPIRED')
        ]);
    }
}
