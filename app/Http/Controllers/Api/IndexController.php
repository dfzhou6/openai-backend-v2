<?php

namespace App\Http\Controllers\Api;

use App\Biz\IndexBiz;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexRequest;
use App\Utils\CommonUtils;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function Index(IndexRequest $request)
    {
        return IndexBiz::Index($request);
    }
}
