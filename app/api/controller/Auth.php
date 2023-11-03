<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\service\TokenService;
use think\App;
use think\facade\Request;

class Auth extends BaseController
{
    protected  $notCheckUrl = [
        '/api/index/eventMap?op=refresh'
    ];

    public function __construct(App $app, Request $request)
    {
        parent::__construct($app);

        //获取请求头中的token
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:*');

        $token = $request::param('access_token');
        $res = TokenService::verifyToken($token);
        if (!$res) {
            return error("登录未授权", 401);
        }

        $loginToken = env('app.user_login_token');
        if ($res['uuid'] != $loginToken) {
            return error("登录账号错误", 401);
        }
    }
}
