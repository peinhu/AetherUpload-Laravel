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

        $origin = $request->server('HTTP_ORIGIN') ?: '';

        if ( in_array($origin, ConfigMapper::get('distributed_deployment_allow_origin')) ) {
            $response->header('Access-Control-Allow-Origin', $origin); # 允许的来源域名
            $response->header('Access-Control-Allow-Headers', 'X-CSRF-TOKEN'); # 允许的请求头部字段
            $response->header('Access-Control-Allow-Methods', 'POST, OPTIONS'); # 允许的请求方法
            $response->header('Access-Control-Allow-Credentials', 'true'); # 是否允许携带cookie
            $response->header('Access-Control-Max-Age', '3600'); # 预检请求缓存时间
            # 添加其它自定义内容
        }


        return $response;
    }
}
