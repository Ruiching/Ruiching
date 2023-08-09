<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\model\User;
use app\service\TokenService;
use think\App;
use think\facade\Request;

class Auth extends BaseController
{
    // 用户
    protected $user;

    // 登陆验证
    public function __construct(App $app, Request $request)
    {
        parent::__construct($app);

        header('Access-Control-Allow-Origin:*');
        $token = $request::param('access_token');
        if (!$token) {
            return error('令牌错误');
        }
        $res = TokenService::verifyToken($token);
        if (!$res) {
            return error("令牌过期", -99);
        }
        $userModel = new User();
        $user = $userModel->where('uuid', $res['uuid'])->find();
        if (!$user) {
            return error('用户不存在');
        }
        $this->user = $user;
    }
}
