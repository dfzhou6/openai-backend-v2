<?php

namespace App\Http\Middleware;

use App\Biz\LoginBiz;
use Closure;
use Illuminate\Http\Request;

class IsUserLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $rsp = LoginBiz::Check($request);
        if ($rsp->getData(true)['code'] != 0) {
            return $rsp;
        }
        return $next($request);
    }
}
