<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\service\TokenService;
use think\facade\Request;
use think\facade\Validate;
use think\facade\View;

class Login extends BaseController
{
    public function index(Request $request)
    {
        if($request::isPost()) {
            $validate = Validate::rule([
                'username|账号'  => 'require',
                'password|密码' => 'require',
            ]);
            if (!$validate->check($request::post())) {
                $this->error($validate->getError());
            }

            if ($request::param('username') != env('app.user_login_name')) {
                return error("账号或密码错误");
            }
            if ($request::param('password') != env('app.user_login_password')) {
                return error("账号或密码错误");
            }

            $token = TokenService::generateToken(env('app.user_login_token'));
            return success('登录成功', $token);
        }
        return error("请求错误");
    }
}
