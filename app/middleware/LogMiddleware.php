<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Log;

class LogMiddleware
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $url = $request->url(true);
        if (strpos($url, 'admin') === false) {
            return $next($request);
        }
        $host = $request->ip() . ' ' . $request->method() . ' ' . $url;
        $logInfo = [
            "[ HOST ] $host",
            '[ HEADER ] ' . var_export($request->header(), true),
            '[ PARAM ] ' . var_export($request->param(), true),
            '==============================================================',
        ];
        $logInfo = implode(PHP_EOL, $logInfo) . PHP_EOL;
        Log::record($logInfo, 'info');
        return $next($request);
    }
}
