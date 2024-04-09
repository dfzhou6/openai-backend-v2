<?php

namespace App\Biz;

use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use App\Utils\CommonUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LoginBiz
{
    public static function Check(Request $request)
    {
        $username = $request->get('username');
        $token = $request->get('token');
        if (empty($username)) {
            return CommonUtils::RspError('请重新登录');
        }

        $user = User::firstWhere('username', $username);
        if (empty($user)) {
            return CommonUtils::RspError('请重新登录');
        }
        if ($user->token !== $token) {
            return CommonUtils::RspError('已在其他地方登录，请重新登录');
        }
        if ($user->token_expire_time < time()) {
            return CommonUtils::RspError('登录已失效，请重新登录');
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

        // fe: bcrypt.hashSync(md5('si1ow4ox_' + this.password), 10)
        if (!password_verify($user->password, $password)) {
            return CommonUtils::RspError('user username or password incorrect');
        }

        $user->token = Str::uuid()->toString();
        $user->last_login_time = time();
        $user->token_expire_time = $user->last_login_time + config('constants.USER_LOGIN_EXPIRED');

        return CommonUtils::RspSuccess([
            'username' => $user->username,
            'last_login_time' => date('Y-m-d H:i:s', $user->last_login_time),
            'token_expire_time' => $user->token_expire_time,
            'token' => $user->token,
        ]);
    }
}
