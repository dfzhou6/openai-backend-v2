<?php

namespace App\Biz;

use App\Http\Requests\Api\IndexRequest;
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
        $reqId = $request->get('req_id');
        $question = $request->get('question');

        // check login
        $loginKey = sprintf(config('constants.USER_LOGIN_KEY'), $username);
        if (!Cache::has($loginKey)) {
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
                return;
            }

            echo $data;
            $clean = str_replace("data: ", "", $data);
            $arr = json_decode($clean, true);
            if ($data != "data: [DONE]\n\n" && isset($arr['choices'][0]['delta']['content'])) {
                $rspData .= $arr['choices'][0]['delta']['content'];
            }

            echo PHP_EOL;
            ob_flush();
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
}
