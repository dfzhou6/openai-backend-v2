<?php

namespace App\Http\Controllers\Api;

use App\Biz\WechatBiz;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function Index(Request $request)
    {
        $pong = WechatBiz::Pong($request);
        if (!empty($pong)) {
            return $pong;
        }

        $wechatObj = simplexml_load_string($request->getContent(), 'SimpleXMLElement', LIBXML_NOCDATA);

        switch ($wechatObj->MsgType) {
            case 'text':
                return WechatBiz::Text($wechatObj);
            case 'event':
                $event = $wechatObj->Event;
                if ($event == 'subscribe') {
                    return WechatBiz::SubscribeEvent($wechatObj);
                } elseif ($event == 'unsubscribe') {
                    return WechatBiz::UnSubscribeEvent($wechatObj);
                }
        }

        return '';
    }
}
