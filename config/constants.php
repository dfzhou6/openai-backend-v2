<?php

return [
    'REPLY_TEXT_TPL' => <<<DATA
    <xml>
      <ToUserName><![CDATA[%s]]></ToUserName>
      <FromUserName><![CDATA[%s]]></FromUserName>
      <CreateTime>%s</CreateTime>
      <MsgType><![CDATA[text]]></MsgType>
      <Content><![CDATA[%s]]></Content>
    </xml>
    DATA,
    'SUBSCRIBE_MSG' => <<<DATA
    感谢关注 "Felix玩转AI"
    如需获取免费GPT账号
    请回复 "gpt"
    GPT网址：https://ai-fozhu.cn
    DATA,
    'NEW_USER_INFO' => <<<DATA
    账号：%s
    密码：%s
    备注：密码无法修改，请妥善保存，如遇到问题，请联系微信：wx1371105743
    DATA,
    'NEW_USER_INFO_DUMPLICATED' => <<<DATA
    账号已存在：%s
    备注：密码无法修改，请妥善保存，如遇到问题，请联系微信：wx1371105743
    DATA,
    'USER_LOGIN_EXPIRED' => 3600*24*7,
    'USER_LOGIN_KEY' => 'user:login:%s',
    'USER_CHAT_KEY' => 'user:chat:%s-%s',
    'USER_CHAT_MSG_EXPIRED' => 3600 * 4,
    'USER_CHAT_MSG_LEN' => 10,
];
