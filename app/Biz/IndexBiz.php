<?php

namespace App\Biz;

use App\Http\Requests\Api\HelloMsgRequest;
use App\Http\Requests\Api\IndexRequest;
use App\Models\Config;
use App\Models\User;
use App\Utils\CommonUtils;
use Illuminate\Support\Facades\Cache;
use Orhanerday\OpenAi\OpenAi;

class IndexBiz
{
    public static function Index(IndexRequest $request)
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:*');
        header('Cache-Control: no-cache');
        header('Content-type: text/event-stream');

        $username = $request->get('username');
        $token = $request->get('token');
        $reqId = $request->get('req_id');
        $question = $request->get('question');

        // check login
        $user = User::firstWhere('username', $username);
        if (empty($user)) {
            return;
        }
        if ($user->token !== $token) {
            return;
        }
        if ($user->token_expire_time < time()) {
            return;
        }

        // get openai api key
        $openaiApiKey = env('OPENAI_API_KEY');
        if (empty($openaiApiKey)) {
            return;
        }

        // get chat history
        $chatKey = sprintf(config('constants.USER_CHAT_KEY'), $username, $reqId);
        $chatInfoJson = Cache::get($chatKey);
        if (!empty($chatInfoJson)) {
            $messages = json_decode($chatInfoJson, true);
        } else {
            $messages = [];
        }
        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        $opts = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 1.0,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'stream' => true
        ];
        $rspData = "";
        $openAi = new OpenAi($openaiApiKey);
        $openAi->chat($opts, function ($curl_info, $data) use (&$rspData) {
            $obj = json_decode($data);
            if ($obj && $obj->error->message != '') {

            } else {
                echo $data;
                $clean = str_replace("data: ", "", $data);
                $arr = json_decode($clean, true);
                if ($data != "data: [DONE]\n\n" && isset($arr['choices'][0]['delta']['content'])) {
                    $rspData .= $arr['choices'][0]['delta']['content'];
                }
            }

            echo PHP_EOL;
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            return strlen($data);
        });

        if (strlen($rspData) > 0) {
            $messages[] = [
                'role' => 'assistant',
                'content' => $rspData,
            ];
            $len = count($messages);
            $msgLen = config('constants.USER_CHAT_MSG_LEN');
            $msgExpired = config('constants.USER_CHAT_MSG_EXPIRED');
            $offset =  $len - $msgLen > 0 ? $len - $msgLen : 0;
            $messages = array_slice($messages, $offset, $msgLen);
            Cache::put($chatKey, json_encode($messages), $msgExpired);
        }
    }

    public static function UpdateHelloMsg(HelloMsgRequest $request)
    {
        $cfg = Config::where('key', 'hello_msg')->where('status', 1)->first();
        if (!$cfg) {
            $cfg = new Config();
            $cfg->key = 'hello_msg';
            $cfg->status = 1;
        }

        $value = [
            'words' => $request->input('words'),
            'source' => $request->input('source'),
        ];
        $cfg->value = json_encode($value, JSON_UNESCAPED_UNICODE);
        $cfg->save();

        return CommonUtils::RspSuccess($value);
    }

    public static function GetHelloMsg()
    {
        $cfg = Config::where('key', 'hello_msg')->where('status', 1)->first();
        $value = json_decode($cfg->value, true);
        return CommonUtils::RspSuccess([
            'words' => $value['words'],
            'source' => $value['source']
        ]);
    }
}
