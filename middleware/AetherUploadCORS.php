<?php

namespace App\Http\Middleware;

use Closure;
use AetherUpload\ConfigMapper;

class AetherUploadCORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->header('Access-Control-Allow-Origin', ConfigMapper::get('distributed_deployment_web_hosts'));//允许的来源域名
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, X-Requested-With');//允许的自定义头部参数
        $response->header('Access-Control-Allow-Methods', 'POST, OPTIONS');//允许的请求方法
        $response->header('Access-Control-Allow-Credentials', 'true');//是否允许携带cookie
        //添加其它自定义内容

        return $response;
    }
}
