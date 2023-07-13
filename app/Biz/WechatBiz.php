<?php

namespace App\Biz;

use App\Models\User;
use App\Utils\CommonUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class WechatBiz
{
    public static function Pong(Request $request)
    {
        if ($request->method() == 'GET' && $request->has('echostr')) {
            $signature = $request->get('signature');
            $timestamp = $request->get('timestamp');
            $nonce = $request->get('nonce');
            $echoStr = $request->get('echostr');

            $tmpArr = [env('WX_TOKEN'), $timestamp, $nonce];
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);

            if($tmpStr === $signature) {
                return $echoStr;
            }
        }

        return '';
    }

    public static function SubscribeEvent(SimpleXMLElement $wechatObj)
    {
        Log::info(sprintf("open_id %s subscribe", $wechatObj->FromUserName));
        $curTime = time();
        $subscribeMsg = config('constants.SUBSCRIBE_MSG');
        $rsp = sprintf(config('constants.REPLY_TEXT_TPL'), $wechatObj->FromUserName, $wechatObj->ToUserName, $curTime, $subscribeMsg);
        return $rsp;
    }

    public static function UnSubscribeEvent(SimpleXMLElement $wechatObj)
    {
        Log::info(sprintf("open_id %s unsubscribe", $wechatObj->FromUserName));
        return '';
    }

    public static function Text(SimpleXMLElement $wechatObj)
    {
        $content = strtolower(trim($wechatObj->Content));
        if (empty($content) || $content != 'gpt') {
            return '';
        }

        $rspContent = '';
        $curTime = time();
        $username = sprintf("%s%s", env('DB_USERNAME_PREFIX', 'gpt_'), CommonUtils::RandomStr());
        $user = User::firstWhere('open_id', $wechatObj->FromUserName);
        if (!empty($user)) {
            $rspContent = sprintf(config('constants.NEW_USER_INFO_DUMPLICATED'), $user->username);
        } else {
            $password = CommonUtils::GenPwd();
            $user = new User();
            $user->open_id = $wechatObj->FromUserName;
            $user->username = $username;
            $user->password = CommonUtils::Md5Pwd($password);
            $user->status = 1;
            $user->create_time = $curTime;
            $user->update_time = $curTime;
            $user->last_login_time = $curTime;
            $user->save();
            $rspContent = sprintf(config('constants.NEW_USER_INFO'), $user->username, $password);
        }

        $rspMsg = sprintf(config('constants.REPLY_TEXT_TPL'), $wechatObj->FromUserName, $wechatObj->ToUserName, $curTime, $rspContent);

        return $rspMsg;
    }
}
