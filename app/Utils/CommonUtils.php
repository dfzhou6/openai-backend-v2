<?php

namespace App\Utils;

class CommonUtils
{
    public static function RandomStr($length = 10)
    {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_str = '';
        for ($i = 0; $i < $length; $i++) {
            $random_str .= $str[mt_rand(0, strlen($str) - 1)];
        }
        return $random_str;
    }

    public static function GenPwd()
    {
        return self::RandomStr(env('DB_PASSWORD_MIN_LEN', 8));
    }

    public static function Md5Pwd($password)
    {
        return md5(sprintf("%s_%s", env('DB_PASSWORD_SALT', 'si1ow4ox'), $password));
    }

    public static function RspSuccess($data)
    {
        $rsp = [
            'code' => 0,
            'msg' => 'success',
            'data' => $data,
        ];
        return response()->json($rsp);
    }

    public static function RspError($msg)
    {
        $rsp = [
            'code' => 1,
            'msg' => $msg,
            'data' => null,
        ];
        return response()->json($rsp);
    }
}
